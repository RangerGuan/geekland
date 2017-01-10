<?php
define('RSS2', 1, true);
define('JSON', 2, true);
define('JSONP', 3, true);

 /**
 * Univarsel Feed Writer class
 *
 * Generate RSS2 or JSON (original: RSS 1.0, RSS2.0 and ATOM Feed)
 *
 * Modified for FiveFilters.org's Full-Text RSS project
 * to allow for inclusion of hubs, JSON output. 
 * Stripped RSS1 and ATOM support.
 *                             
 * @package     UnivarselFeedWriter
 * @author      Anis uddin Ahmad <anisniit@gmail.com>
 * @link        http://www.ajaxray.com/projects/rss
 */
 class FeedWriter
 {
	 private $self          = null;     // self URL - http://feed2.w3.org/docs/warning/MissingAtomSelfLink.html
	 private $alternate     = array();  // alternate URL and title
	 private $related       = array();  // related URL and title	 
	 private $hubs          = array();  // PubSubHubbub hubs
	 private $channels      = array();  // Collection of channel elements
	 private $items         = array();  // Collection of items as object of FeedItem class.
	 private $data          = array();  // Store some other version wise data
	 private $CDATAEncoding = array();  // The tag names which have to encoded as CDATA
	 private $xsl			= null;		// stylesheet to render RSS (used by Chrome)
	 private $json			= null;		// JSON object
	 private $simplejson	= false;
	 
	 private $version   = null; 
	
	/**
	* Constructor
	* 
	* @param    constant    the version constant (RSS2 or JSON).       
	*/ 
	function __construct($version = RSS2)
	{	
		$this->version = $version;
			
		// Setting default value for assential channel elements
		$this->channels['title']        = $version . ' Feed';
		$this->channels['link']         = 'http://www.ajaxray.com/blog';
				
		//Tag names to encode in CDATA
		$this->CDATAEncoding = array('description', 'content:encoded', 'content', 'subtitle', 'summary');
	}
	
	public function setFormat($format) {
		$this->version = $format;
	}

	// Start # public functions ---------------------------------------------
	
	public function enableSimpleJson($enable=true) {
		$this->simplejson = $enable;
	}

	/**
	* Set a channel element
	* @access   public
	* @param    srting  name of the channel tag
	* @param    string  content of the channel tag
	* @return   void
	*/
	public function setChannelElement($elementName, $content)
	{
		$this->channels[$elementName] = $content ;
	}
	
	/**
	* Set multiple channel elements from an array. Array elements 
	* should be 'channelName' => 'channelContent' format.
	* 
	* @access   public
	* @param    array   array of channels
	* @return   void
	*/
	public function setChannelElementsFromArray($elementArray)
	{
		if(! is_array($elementArray)) return;
		foreach ($elementArray as $elementName => $content) 
		{
			$this->setChannelElement($elementName, $content);
		}
	}
	
	/**
	* Generate the actual RSS/JSON file
	* 
	* @access   public
	* @return   void
	*/ 
	public function generateFeed()
	{
		if ($this->version == RSS2) {
			header('Content-type: text/xml; charset=UTF-8');
			// this line prevents Chrome 20 from prompting download
			// used by Google: https://news.google.com/news/feeds?ned=us&topic=b&output=rss
			header('X-content-type-options: nosniff');
		} elseif ($this->version == JSON) {
			header('Content-type: application/json; charset=UTF-8');
			$this->json = new stdClass();
		} elseif ($this->version == JSONP) {
			header('Content-type: application/javascript; charset=UTF-8');
			$this->json = new stdClass();
		}
		$this->printHead();
		$this->printChannels();
		$this->printItems();
		$this->printTale();
		if ($this->version == JSON || $this->version == JSONP) {
			if (!$this->simplejson) {
				echo json_encode($this->json);
			} else {
				$simplejson = new stdClass();
				if (is_array($this->json->rss['channel']->item)) {
					// get first item
					$jsonitem = $this->json->rss['channel']->item[0];
				} else {
					$jsonitem = $this->json->rss['channel']->item;
				}
				// defaults
				$simplejson->title = null;
				$simplejson->excerpt = null;
				$simplejson->date = null;
				$simplejson->author = null;
				$simplejson->language = null;
				$simplejson->url = null;
				$simplejson->effective_url = null;
				$simplejson->content = null;
				// actual values
				$simplejson->url = $jsonitem->link;
				$simplejson->effective_url = $jsonitem->dc_identifier;
				if (isset($jsonitem->title)) $simplejson->title = $jsonitem->title;
				if (isset($jsonitem->dc_language)) $simplejson->language = $jsonitem->dc_language;
				if (isset($jsonitem->content_encoded)) {
					$simplejson->content = $jsonitem->content_encoded;
					if (isset($jsonitem->description)) {
						$simplejson->excerpt = $jsonitem->description;
					}
				} else {
					$simplejson->content = $jsonitem->description;
				}
				if (isset($jsonitem->dc_creator)) {
					$simplejson->author = $jsonitem->dc_creator;
				}
				if (isset($jsonitem->pubDate)) {
					$simplejson->date = gmdate(DATE_ATOM, strtotime($jsonitem->pubDate));
				}
				echo json_encode($simplejson);
			}
		}
	}
	
	public function &getItems()
	{
		return $this->items;
	}
	
	/**
	* Create a new FeedItem.
	* 
	* @access   public
	* @return   object  instance of FeedItem class
	*/
	public function createNewItem()
	{
		$Item = new FeedItem($this->version);
		return $Item;
	}
	
	/**
	* Add a FeedItem to the main class
	* 
	* @access   public
	* @param    object  instance of FeedItem class
	* @return   void
	*/
	public function addItem($feedItem)
	{
		$this->items[] = $feedItem;    
	}
	
	// Wrapper functions -------------------------------------------------------------------
	
	/**
	* Set the 'title' channel element
	* 
	* @access   public
	* @param    srting  value of 'title' channel tag
	* @return   void
	*/
	public function setTitle($title)
	{
		$this->setChannelElement('title', $title);
	}
	
	/**
	* Add a hub to the channel element
	* 
	* @access   public
	* @param    string URL
	* @return   void
	*/
	public function addHub($hub)
	{
		$this->hubs[] = $hub;    
	}
	
	/**
	* Set XSL URL
	* 
	* @access   public
	* @param    string URL
	* @return   void
	*/
	public function setXsl($xsl)
	{
		$this->xsl = $xsl;    
	}

	/**
	* Set TTL
	* 
	* @access   public
	* @param    int time to live (minutes)
	* @return   void
	*/
	public function setTtl($ttl)
	{
		$this->setChannelElement('ttl', (int)$ttl);
	}		
	
	/**
	* Set self URL
	* 
	* @access   public
	* @param    string URL
	* @return   void
	*/
	public function setSelf($url)
	{
		$this->self = $url;    
	}

	/**
	* Set alternate URL
	* 
	* @access   public
	* @param    string URL
	* @param    string title
	* @return   void
	*/
	public function setAlternate($url, $title)
	{
		$this->alternate = array('url'=>$url, 'title'=>$title);    
	}

	/**
	* Set related URL
	* 
	* @access   public
	* @param    string URL
	* @param    string title
	* @return   void
	*/
	public function setRelated($url, $title)
	{
		$this->related = array('url'=>$url, 'title'=>$title);    
	}			
	
	/**
	* Set the 'description' channel element
	* 
	* @access   public
	* @param    srting  value of 'description' channel tag
	* @return   void
	*/
	public function setDescription($description)
	{ 
		$this->setChannelElement('description', $description);
	}
	
	/**
	* Set the 'link' channel element
	* 
	* @access   public
	* @param    srting  value of 'link' channel tag
	* @return   void
	*/
	public function setLink($link)
	{
		$this->setChannelElement('link', $link);
	}
	
	/**
	* Set the 'image' channel element
	* 
	* @access   public
	* @param    srting  title of image
	* @param    srting  link url of the imahe
	* @param    srting  path url of the image
	* @return   void
	*/
	public function setImage($title, $link, $url)
	{
		$this->setChannelElement('image', array('title'=>$title, 'link'=>$link, 'url'=>$url));
	}
	
	// End # public functions ----------------------------------------------
	
	// Start # private functions ----------------------------------------------
	
	/**
	* Prints the xml and rss namespace
	* 
	* @access   private
	* @return   void
	*/
	private function printHead()
	{
		if ($this->version == RSS2)
		{
			$out  = '<?xml version="1.0" encoding="utf-8"?>'."\n";
			if ($this->xsl) $out .= '<?xml-stylesheet type="text/xsl" href="'.htmlspecialchars($this->xsl).'"?>' . PHP_EOL;
			$out .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/">' . PHP_EOL;
			echo $out;
		}
		elseif ($this->version == JSON || $this->version == JSONP)
		{
			$this->json->rss = array('@attributes' => array('version' => '2.0'));
		}
	}
	
	/**
	* Closes the open tags at the end of file
	* 
	* @access   private
	* @return   void
	*/
	private function printTale()
	{
		if ($this->version == RSS2)
		{
			echo '</channel>',PHP_EOL,'</rss>'; 
		}    
		// do nothing for JSON
	}

	/**
	* Creates a single node as xml format
	* 
	* @access   private
	* @param    string  name of the tag
	* @param    mixed   tag value as string or array of nested tags in 'tagName' => 'tagValue' format
	* @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
	* @return   string  formatted xml tag
	*/
	private function makeNode($tagName, $tagContent, $attributes = null)
	{        
		if ($this->version == RSS2)
		{
			$nodeText = '';
			$attrText = '';
			if (is_array($attributes))
			{
				foreach ($attributes as $key => $value) 
				{
					$attrText .= " $key=\"".htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false)."\" ";
				}
			}
			$nodeText .= "<{$tagName}{$attrText}>";
			if (is_array($tagContent))
			{ 
				foreach ($tagContent as $key => $value) 
				{
					$nodeText .= $this->makeNode($key, $value);
				}
			}
			else
			{
				//$nodeText .= (in_array($tagName, $this->CDATAEncoding))? $tagContent : htmlentities($tagContent);
				$nodeText .= htmlspecialchars($tagContent, ENT_COMPAT, 'UTF-8', false);
			}           
			//$nodeText .= (in_array($tagName, $this->CDATAEncoding))? "]]></$tagName>" : "</$tagName>";
			$nodeText .= "</$tagName>";
			return $nodeText . PHP_EOL;
		}
		elseif ($this->version == JSON || $this->version == JSONP)
		{
			$tagName = (string)$tagName;
			$tagName = strtr($tagName, ':', '_');
			$node = null;
			if (!$tagContent && is_array($attributes) && count($attributes))
			{
				$node = array('@attributes' => $this->json_keys($attributes));
			} else {
				if (is_array($tagContent)) {
					$node = $this->json_keys($tagContent);
				} else {
					$node = $tagContent;
				}
			}
			return $node;
		}
		return ''; // should not get here
	}
	
	private function json_keys(array $array) {
		$new = array();
		foreach ($array as $key => $val) {
			if (is_string($key)) $key = strtr($key, ':', '_');
			if (is_array($val)) {
				$new[$key] = $this->json_keys($val);
			} else {
				$new[$key] = $val;
			}
		}
		return $new;
	}
	
	/**
	* @desc     Print channels
	* @access   private
	* @return   void
	*/
	private function printChannels()
	{
		//Start channel tag
		if ($this->version == RSS2) {
			echo '<channel>' . PHP_EOL;    
			// add hubs
			foreach ($this->hubs as $hub) {
				//echo $this->makeNode('link', '', array('rel'=>'hub', 'href'=>$hub, 'xmlns'=>'http://www.w3.org/2005/Atom'));
				echo '<atom:link rel="hub"  href="'.htmlspecialchars($hub).'" />' . PHP_EOL;
			}
			// add self
			if (isset($this->self)) {
				//echo $this->makeNode('link', '', array('rel'=>'self', 'href'=>$this->self, 'xmlns'=>'http://www.w3.org/2005/Atom'));
				echo '<atom:link rel="self" href="'.htmlspecialchars($this->self).'" />' . PHP_EOL;
			}
			// add alternate
			if (isset($this->alternate)) {
				echo '<atom:link rel="alternate" title="'.htmlspecialchars($this->alternate['title']).'" href="'.htmlspecialchars($this->alternate['url']).'" />' . PHP_EOL;
			}
			// add related
			if (isset($this->related)) {
				echo '<atom:link rel="related" title="'.htmlspecialchars($this->related['title']).'" href="'.htmlspecialchars($this->related['url']).'" />' . PHP_EOL;
			}			
			//Print Items of channel
			foreach ($this->channels as $key => $value) 
			{
				echo $this->makeNode($key, $value);
			}
		} elseif ($this->version == JSON || $this->version == JSONP) {
			$this->json->rss['channel'] = (object)$this->json_keys($this->channels);
		}
	}
	
	/**
	* Prints formatted feed items
	* 
	* @access   private
	* @return   void
	*/
	private function printItems()
	{    
		foreach ($this->items as $item) {
			$itemElements = $item->getElements();
			
			echo $this->startItem();
			
			if ($this->version == JSON || $this->version == JSONP) {
				$json_item = array();
			}
			
			foreach ($itemElements as $thisElement) {
				foreach ($thisElement as $instance) {			
					if ($this->version == RSS2) {
						echo $this->makeNode($instance['name'], $instance['content'], $instance['attributes']);
					} elseif ($this->version == JSON || $this->version == JSONP) {
						$_json_node = $this->makeNode($instance['name'], $instance['content'], $instance['attributes']);
						if (count($thisElement) > 1) {
							$json_item[strtr($instance['name'], ':', '_')][] = $_json_node;
						} else {
							$json_item[strtr($instance['name'], ':', '_')] = $_json_node;
						}
					}
				}
			}
			echo $this->endItem();
			if ($this->version == JSON || $this->version == JSONP) {
				if (count($this->items) > 1) {
					$this->json->rss['channel']->item[] = (object)$json_item;
				} else {
					$this->json->rss['channel']->item = (object)$json_item;
				}
			}
		}
	}
	
	/**
	* Make the starting tag of channels
	* 
	* @access   private
	* @return   void
	*/
	private function startItem()
	{
		if ($this->version == RSS2)
		{
			echo '<item>' . PHP_EOL; 
		}    
		// nothing for JSON
	}
	
	/**
	* Closes feed item tag
	* 
	* @access   private
	* @return   void
	*/
	private function endItem()
	{
		if ($this->version == RSS2)
		{
			echo '</item>' . PHP_EOL; 
		}    
		// nothing for JSON
	}
	
	// End # private functions ----------------------------------------------
 }