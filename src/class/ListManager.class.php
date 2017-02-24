<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Text.lib.php');
require_once('Files.lib.php');

/**
 * The Lists Manager includes functions to build, manage and work with lists
 * 
 * Lists are a tool to manage lists of things, whatever they may be. They could be News categories, product sizes, or any other set of 
 * information that is stored in a linear collection. This library allows each list entry to contain meta-data as well as a simple name.
 * The Lists are built directly into the database, either manualy, or from an install script, but are managed in the CMS.
 * 
 * A set of fields is defined for each List. Fields can be of different data types, which defines how the CMS collects and manages the data.
 * Each item in the list must then contain a value for each field defined for that List.
 * 
 * This class can be extended to provide additional functionality. To do this, also ensure that the extendListsClass global setting is set to the name of your class.
 * 
 * This class replaces the Lists.lib.php library
 * 
 * @package Classes
 * @subpackage Lists
 * @since 2.4
 **/
class ListManager{
	
	/**
	 * Fetch an array containing the details of all available Lists
	 *
	 * @return array A multi-dimentional array containing the details of all Lists in the database
	 *
	 */
	public function getLists(){
		$res = $GLOBALS['db']->select("select * from lists");
		return $res;
	}
	
	/**
	 * Get the details of a specific List
	 *
	 * @param mixed $id The ID or Code of the List
	 * @return array The details of the List
	 *
	 */
	public function getList($id){
		if(empty($id)) return false;	
		$id = mysql_real_escape_string($id);
		$res = $GLOBALS['db']->selectrow("select * from lists where id = '$id' or code = '$id'");
		if($res){
			$res['fields'] = $GLOBALS['db']->selectindex("select * from listfields where listid = {$res['id']} order by position",'id');
			$res['items'] = $GLOBALS['db']->selectindex("select * from listitems where listid = {$res['id']} order by position",'id');
			if($res['items']){
				foreach($res['items'] as &$item){
					$item['values'] = $GLOBALS['db']->selectindex("select * from listitemfields where itemid = {$item['id']}",'fieldid');
					if($item['values']){
						foreach($item['values'] as $fieldid=>$value){
							$item[$res['fields'][$fieldid]['name']] = $value['value'];	
						}
					}
				}	
			}
		}
		return $res;	
	}
	
	/**
	 * Returns the details of a particular item in a list
	 *
	 * @param int $id The ID of the list item in the listitems table
	 * @return array The details of the list item
	 *
	 */
	public function getListItem($id){
		if(empty($id) || !is_numeric($id)) $id = 0;
		$res = $GLOBALS['db']->selectrow("select i.*, l.code from listitems i inner join lists l on l.id = i.listid where i.id = $id");
		if($res){
			$res['values'] = $GLOBALS['db']->selectindex("select i.*,f.name from listitemfields i inner join listfields f on f.id = i.fieldid where i.itemid = $id",'fieldid');
			if(is_array($res['values'])){
				foreach($res['values'] as $field){
					$res[$field['name']] = $field['value'];	
				}
			}
		}
		return $res;	
	}
	
	/**
	 * Gets the highest position value for the given List. This is usually used to determine the position of a new List item.
	 *
	 * @param int $id The ID of the required List
	 * @return int The highest existing position value for the List
	 *
	 */
	public function getMaxListPosition($id){
		if(empty($id) || !is_numeric($id)) $id = 0;
		$sql = "select max(position) from listitems where listid = $id";
		$res = $GLOBALS['db']->selectsingle($sql);
		if(empty($res) || !is_numeric($res)) return 0;
		return $res;
	}
	
	/**
	 * Add a new entry to a List
	 *
	 * @param int $listid the ID of the List
	 * @param string $name The name of the entry
	 * @param array $data An array of the data for each of the fields in the List for this entry
	 * @param string $error A container for any errors that might happen during processing
	 * @return int The ID of the new entry
	 *
	 */
	public function createListEntry($listid, $name, $data, &$error){
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['listid'] = $listid;
		$values['name'] = $name;
		$values['position'] = $this->getMaxListPosition($listid) + 1;
		$id = $GLOBALS['db']->insert('listitems',$values);
		if($id === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		if(is_array($data)){
			foreach($data as $fieldid=>$value){
				$values = array();
				$values['itemid'] = $id;
				$values['fieldid'] = $fieldid;
				$values['value'] = $value;
				$res = $GLOBALS['db']->insert('listitemfields',$values);
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;
				}
			}
		}
		$GLOBALS['db']->committransaction();
		return $id;
	}
	
	/**
	 * Update an existing List entry
	 *
	 * @param int $entryid The ID of the entry
	 * @param string $name The name of the entry
	 * @param array $data An array of the data for each of the fields in the List for this entry
	 * @param string $error A container variable for any errors that might occur during processing
	 * @return bool True for success, otherwise false.
	 *
	 */
	public function updateListEntry($entryid, $name, $data, &$error){
		if(empty($entryid) || !is_numeric($entryid)){
			$error = 'Invalid entry ID';
			return false;
		}
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['name'] = $name;
		$res = $GLOBALS['db']->update('listitems',$values,array('id'=>$entryid));
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$res = $GLOBALS['db']->delete('listitemfields',$entryid,'itemid');
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		if(is_array($data)){
			foreach($data as $fieldid=>$value){
				$values = array();
				$values['itemid'] = $entryid;
				$values['fieldid'] = $fieldid;
				$values['value'] = $value;
				$res = $GLOBALS['db']->insert('listitemfields',$values);
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;
				}
			}
		}
		$GLOBALS['db']->committransaction();
		return true;
	}
	
	/**
	 * Move a List entry to a new position within the List. 
	 * 
	 * This method is not intended to be called directly - use moveListEntryUp or moveListEntryDown instead
	 *
	 * @param array $item The item to be moved
	 * @param int $to The target position
	 * @param string $error A container variable for any errors that might occur during processing
	 * @return bool True for success, otherwise false
	 *
	 */
	public function moveListEntry($item, $to, &$error){
		$GLOBALS['db']->begintransaction();
		$res = $GLOBALS['db']->update('listitems',array('position'=>999999),array('position'=>$to,'listid'=>$item['listid']));
		if(!$res){
			$error = 'Could not reset target position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}	
		$res = $GLOBALS['db']->update('listitems',array('position'=>$to),array('position'=>$item['position'],'listid'=>$item['listid']));
		if(!$res){
			$error = 'Could not reset source position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}	
		$res = $GLOBALS['db']->update('listitems',array('position'=>$item['position']),array('position'=>999999,'listid'=>$item['listid']));
		if(!$res){
			$error = 'Could not reset target to source.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return true;
	}
	
	/**
	 * Move an item up one position in the List
	 *
	 * @param int $id The ID of the List item
	 * @param string $error A container variable for any errors that might occur during processing
	 * @return bool True for success, otherwise false
	 *
	 */
	public function moveListEntryUp($id,&$error){
		$item = $this->getListItem($id);
		if($item['position'] <= 1){
			$error = 'Cannot move the first item up any more.';
			return false;	
		}
		return $this->moveListEntry($item,$item['position']-1,$error);
	}
	
	/**
	 * Move an item down one position in the List
	 *
	 * @param int $id The ID of the List item
	 * @param string $error A container variable for any errors that might occur during processing
	 * @return bool True for success, otherwise false
	 *
	 */
	public function moveListEntryDown($id,&$error){
		$item = $this->getListItem($id);
		if($item['position'] >= $this->getMaxListPosition($item['listid'])){
			$error = 'Cannot move the last item down any more.';
			return false;	
		}
		return $this->moveListEntry($item,$item['position']+1,$error);
	}
	
	/**
	 * Delete an entry from a List
	 *
	 * @param int $id The ID of the List entry
	 * @param string $error A container variable for any errors that may occur during processing
	 * @return bool True on success, otherwise false
	 *
	 */
	public function deleteListEntry($id, &$error){
		if(empty($id) || !is_numeric($id)) $id = 0;
		$GLOBALS['db']->begintransaction();
		$entry = $this->getListItem($id);
		$res = $GLOBALS['db']->delete('listitems',$id);
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$res = $GLOBALS['db']->execute("update listitems set position = position - 1 where  position > " . $entry['position'] . " and listid = " . $entry['listid']);
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return true;
	}
	
	/**
	 * Renders a dropdown control (SELECT tag) containing all the entries in a List
	 *
	 * @param mixed $listid The ID or Code for the required List
	 * @param string $elementid The name and ID of the dropdown control
	 * @param int $selected The ID of the selected entry
	 * @param mixed $showall The text for a "select All" entry, or false for no such entry
	 * @param string $extra Any extra attributes for the dropdown control (eg: class="myclass" style="mystyle: value;")
	 * @return string The HTML fragment defining the dropdown control
	 *
	 */
	public function drawListSingleOption($listid,$elementid,$selected,$showall=false,$extra=null){
		$list = $this->getList($listid);	
		if(empty($list) || empty($list['items'])) return false;
		$res .= '<select name="'.$elementid.'" id="'.$elementid.'" '.$extra.'>';
		if($showall) $res .= '<option value="">'.$showall.'</option>';
		foreach($list['items'] as $item){
			$sel = $item['id']==$selected?' selected':'';
			$res .= '<option value="'.$item['id'].'"'.$sel.'>'.$item['name'].'</option>';
		}
		$res .= '</select>';
		return $res;
	}
	
	/**
	 * Creates an instance of the ListManager class, or a predefined subclass of it. Set the "extendListsClass" global variable with the name of the subclass.
	 *
	 * @return ListManager The instance. Also added to the $GLOBALS set.
	 *
	 */
	final public static function getListManager(){
		if(!empty($GLOBALS['listmanager'])) return $GLOBALS['listmanager'];
		if(!empty($GLOBALS['settings']->extendListsClass) && class_exists($GLOBALS['settings']->extendListsClass) && is_subclass_of($GLOBALS['settings']->extendListsClass,'ListManager')){
			$GLOBALS['listmanager'] = new $GLOBALS['settings']->extendListsClass();
		}else{
			$GLOBALS['listmanager'] = new ListManager();
		}
		return $GLOBALS['listmanager'];
	}
}
?>