<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

// {{{ FeedReader Class
/**
 *  Reads an RSS or ATOM feed
 * 
 * This class was based on:
 * Atom Extractor and Displayer
 * (c) 2007  Scriptol.com - Licence Mozilla 1.1.
 * atomlib.php
 * 
* @package Classes\Feed Reader
 * @since 2.0
 */
class FeedReader{
	
	// {{{ Declarations
	/**
	 * The URL of the RSS or ATOM file
	 *
	 * @var string 
	 */
	public $sourceurl;
	
	/**
	 * An array of channels, containing the content of the feed
	 *
	 * @var array 
	 */
	public $channels;
	#endregion
	
	// {{{ Constructor
	/**
	 * Create a new instance of this class
	 *
	 * @param string $sourceurl The URL of the feed
	 */
	function __construct($sourceurl){
		$this->sourceurl = $sourceurl;
	}
	#endregion
	
	// {{{ Parse Feed
	/**
	 * Worker method. Use this method to fetch and parse the feed
	 *
	 * @param string $cachepath The path to a cache file. If supplied, a copy of the downloaded file will be saved to make future reads faster
	 * @return mixed false for an error, or no return value
	 */
	public function FetchFeed($cachepath = null){
		$doc  = new DOMDocument();
		if(!@$doc->load($this->sourceurl)){
			return false;
		}
		
		if(!empty($cachepath)){
			$content = $doc->save($cachepath);
		}
		
		$first = $doc->documentElement;
		$tagname = $first->tagName;
		
		if($tagname == 'rss'){
			$this->format = 'rss';
			$this->FetchRSSFeed($doc);	
		}else{
			$this->format = 'atom';
			$this->FetchAtomFeed($doc);	
		}
	}
	#endregion
	
	// {{{ ATOM Feed
	/**
	 * Fetch and parse an ATOM feed.
	 *
	 * @param DOMDocument $doc The XML document object created from the source URL
	 */
	private function FetchAtomFeed($doc){
		
		$this->channels = array();
		
		$entries = $doc->getElementsByTagName("entry");
		
		// Processing feed
		
		$tnl = $doc->getElementsByTagName("title");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$title = $tnl->firstChild->data;
		}
		
		$tnl = $doc->getElementsByTagName("link");
		if($tnl->length){
			$tnl = $tnl->item(0);	
			$link = $tnl->getAttribute("href");
		}
		
		$tnl = $doc->getElementsByTagName("subtitle");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$description = $tnl->firstChild->data;
		}
		
		$channel = new FeedChannel($title,$link,$description,'');
		
		// Processing articles
		
		foreach($entries as $entry)
		{
			$y = $this->GetAtomEntry($entry);
			array_push($channel->entries, $y);
		}
		$this->channels[] = $channel;
		
	}
	#endregion
	
	// {{{ RSS Feed
	/**
	 * Fetch and parse an RSS feed
	 *
	 * @param DOMDocument $doc The XML Document object crested from the source URL
	 */
	private function FetchRSSFeed($doc){
		
		$this->channels = array();
		
		$channels = $doc->getElementsByTagName("channel");
		
		foreach($channels as $channel){
			
			// Processing feed
			$entries = $channel->getElementsByTagName("item");
			
			$tnl = $channel->getElementsByTagName("title");
			if($tnl->length){
				$tnl = $tnl->item(0);
				$title = $tnl->firstChild->data;
			}
			
			$tnl = $channel->getElementsByTagName("link");
			if($tnl->length){
				$tnl = $tnl->item(0);	
				$link = $tnl->firstChild->data;
			}
			
			$tnl = $channel->getElementsByTagName("description");
			if($tnl->length){
				$tnl = $tnl->item(0);
				$description = $tnl->firstChild->data;
			}
			
			$feedchannel = new FeedChannel($title,$link,$description);
			
			// Processing articles
			
			foreach($entries as $entry)
			{
				$y = $this->GetRSSEntry($entry);
				array_push($feedchannel->entries, $y);
			}
			$this->channels[] = $feedchannel;
		}
		
	}
	#endregion
	
	// {{{ ATOM Entry
	/**
	 * Process each item in the feed
	 *
	 * @param DOMNode $item The node to parse
	 * @return FeedEntry The resulting FeedEntry object
	 */
	private function GetAtomEntry($item){
		
		$tnl = $item->getElementsByTagName("title");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$title = $tnl->firstChild->data;
		}
		
		$tnl = $item->getElementsByTagName("link");
		foreach($tnl as $ele){
			if($ele->getAttribute('rel') == 'alternate'){
				$link = $ele->getAttribute("href");
				break;
			}
		}
		
		$tnl = $item->getElementsByTagName("summary");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$description = $tnl->firstChild->data;
		}
		
		$tnl = $item->getElementsByTagName("published");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$pubdate = $tnl->firstChild->data;
		}
		
		$tnl = $item->getElementsByTagName("category");
		if($tnl->length){
			$tnl = $tnl->item(0);
			$category = $tnl->getAttribute("term");
		}
		
		$entry = new FeedEntry($title,$link,$description,$pubdate,$category);
		return $entry;
	}
	#endregion
	
	// {{{ RSS Entry
	/**
	 * Process each item in the feed
	 *
	 * @param DOMNode $item The node to parse
	 * @return FeedEntry The resulting FeedEntry object
	 */
	private function GetRSSEntry($item){
		
		$tnl = $item->getElementsByTagName("title");
		$tnl = $tnl->item(0);
		$title = $tnl->firstChild->data;
		
		$tnl = $item->getElementsByTagName("link");
		$tnl = $tnl->item(0);		
		$link = $tnl->firstChild->data;
		
		$tnl = $item->getElementsByTagName("description");
		$tnl = $tnl->item(0);
		$description = $tnl->firstChild->data;
		
		$tnl = $item->getElementsByTagName("pubDate");
		$tnl = $tnl->item(0);
		$pubdate = $tnl->firstChild->data;
		
		$entry = new FeedEntry($title,$link,$description,$pubdate);
		return $entry;
	}
	#endregion
}
#endregion

// {{{ FeedChannel Class
/**
 * A container class for a channel within a feed.
 *
* @package Classes\Feed Reader
 * @since 2.0
 */
class FeedChannel{
	
	// {{{ Declarations
	/**
	 * The title of the channel
	 *
	 * @var string 
	 */
	public $title;
	
	/**
	 * The link URL for this channel
	 *
	 * @var string 
	 */
	public $link;
	
	/**
	 * The description of this channel
	 *
	 * @var string 
	 */
	public $description;
	
	/**
	 * The collection of entries contained in this channel
	 *
	 * @var FeedEntry[] 
	 */
	public $entries;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param string $title The title of the channel
	 * @param string $link The URL of the channel
	 * @param string $description The description of the channel
	 */
	function __construct($title,$link,$description){
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
		$this->entries = array();
	}
	#endregion
}
#endregion

// {{{ FeedEntry Class
/**
 * The container class for a single entry within a feed
 *
* @package Classes\Feed Reader
 * @since 2.0
 */
class FeedEntry{
	
	// {{{ Declarations
	/**
	 * The title of this entry
	 *
	 * @var string 
	 */
	public $title;
	
	/**
	 * The link URL for this entry
	 *
	 * @var string 
	 */
	public $link;
	
	/**
	 * The description of this entry
	 *
	 * @var string 
	 */
	public $description;
	
	/**
	 * The publication date for this entry
	 *
	 * @var string 
	 */
	public $pubdate;
	
	/**
	 * The category of this entry
	 *
	 * @var string 
	 */
	public $category;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param string $title The title of the entry
	 * @param string $link The link URL of the entry
	 * @param string $description The description of the entry
	 * @param string $pubdate The publication date for the entry
	 * @param string $category The category for the entry
	 */
	function __construct($title,$link,$description,$pubdate,$category=null){
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
		$this->pubdate = $pubdate;
		$this->category = $category;
	}
	#endregion
}
#endregion

?>
