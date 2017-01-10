<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
<xsl:output method="html" /> 
<xsl:variable name="title" select="/rss/channel/title"/>
<xsl:variable name="subscribe" select="/rss/channel/atom:link[@rel='related']/@href"/>
<xsl:template match="/">
<html>
  <head>
    <title><xsl:value-of select="$title"/> (full-text feed)</title>
    <link rel="stylesheet" type="text/css" href="css/feed.css" />
  </head>
  <body>
    <div id="explanation">
      <h1><xsl:value-of select="$title"/> <span class="small"> (full-text feed)</span></h1>
      <p>You are viewing an auto-generated full-text <acronym title="Really Simple Syndication">RSS</acronym> feed. RSS feeds allow you to stay up to date with the latest news and features you want from websites.<br /><a href="{$subscribe}">Subscribe to this feed.</a></p>
      <p>Below is the latest content available from this feed.</p>
    </div>
    
    <div id="content">
    <ul>
      <xsl:for-each select="rss/channel/item">
      <div class="article">
        <li><a href="{link}" rel="bookmark"><xsl:value-of disable-output-escaping="yes" select="title"/></a>
			<div>
			<xsl:choose>
				<xsl:when test="content:encoded"><xsl:value-of disable-output-escaping="yes" select="content:encoded" /></xsl:when>
				<xsl:when test="description"><xsl:value-of disable-output-escaping="yes" select="description" /></xsl:when>
			</xsl:choose>
			</div>
		</li>        
      </div>
      </xsl:for-each>
      </ul>
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>