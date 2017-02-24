<?php
/**
 * Ajax Agent for PHP v.0.3. Copyright (c) 2006 ajaxagent.org. 
 * This program is free software; you can redistribute it under the 
 * terms of the GNU General Public License as published by the Free 
 * Software Foundation; either version 2 of the License, or (at your 
 * option) any later version. This program is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the 
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
 * PURPOSE. See the GNU General Public License for more info 
 * at http://www.gnu.org/licenses/gpl.txt
 * 
 * @package Library
 * @subpackage Ajax Agent
 * @since 2.0
 * @author: Steve Hemmady, Anuta Udyawar <contact at ajaxagent dot org>
**/
global $agent,$aa_url;
// Server side Ajax Agent implimentation follows
if (isset($_POST['aa_afunc'])) $aa_afunc = $_POST['aa_afunc']; else $aa_afunc="";
if (isset($_POST['aa_sfunc'])) $aa_sfunc = $_POST['aa_sfunc']; else $aa_sfunc="";
if (isset($_POST['aa_event'])) $aa_event = $_POST['aa_event']; else $aa_event="";
if (isset($_POST['aa_cfunc'])) $aa_cfunc = $_POST['aa_cfunc']; else $aa_cfunc="";
if (isset($_POST['aa_sfunc_args'])) $aa_sfunc_args = $_POST['aa_sfunc_args']; 
else $aa_sfunc_args="";

if($aa_afunc=="call") {
  $agent->call($aa_sfunc, $aa_cfunc, $aa_sfunc_args);
}
  

?>
