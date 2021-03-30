<?php

/**
 * This file is part of urodoz/truncateHTML.
 *
 * (c) Albert Lacarta <urodoz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Urodoz\Truncate;

/**
 * TruncateService
 *
 * @package   org.urodoz.truncatehtml
 * @author    Albert Lacarta <urodoz@gmail.com>
 * @license   http://www.opensource.org/licenses/MIT The MIT License
 */
class TruncateService implements TruncateInterface
{
	/** @var string */
	private $encoding;

	/**
	 * @param string $encoding
	 */
	public function __construct($encoding = 'UTF-8')
	{
		$this->encoding = $encoding;
	}

	/**
	 * Static method to create new instance of {@see Slugify}.
	 *
	 * @return TruncateInterface
	 */
	public static function create($encoding = 'UTF-8')
	{
		return new static($encoding);
	}

	/**
	 * @inheritDoc
	 */
	public function truncate($text, $length = 100, $append = '…')
	{
		// if the plain text is shorter than the maximum length, return the whole text
		if (mb_strlen(preg_replace('/<.*?>/', '', $text), $this->encoding) <= $length)
			return $text;

		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = mb_strlen($append, $this->encoding);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings)
		{
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1]))
			{
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match(
					'/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is',
					$line_matchings[1]
				))
				{
					// do nothing
					// if tag is a closing tag
				}
				else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings))
				{
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags, true);
					if ($pos !== false)
					{
						unset($open_tags[$pos]);
					}
					// if tag is an opening tag
				}
				else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings))
				{
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, mb_strtolower($tag_matchings[1], $this->encoding));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = mb_strlen(
				preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]),
				$this->encoding
			);
			if ($total_length + $content_length > $length)
			{
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all(
					'/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i',
					$line_matchings[2],
					$entities
				))
				{
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity)
					{
						if ($entity[1] + 1 - $entities_length <= $left)
						{
							$left--;
							$entities_length += strlen($entity[0]);
						}
						else
							break;
					}
				}
				$truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length, $this->encoding);
				// maximum lenght is reached, so get off the loop
				break;
			}
			else
			{
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			if ($total_length >= $length)
				break;
		}
		$spacepos = mb_strrpos($truncate, ' ', 0, $this->encoding);
		if (isset($spacepos))
			$truncate = mb_substr($truncate, 0, $spacepos, $this->encoding);

		$truncate .= $append;

		foreach ($open_tags as $tag)
			$truncate .= '</' . $tag . '>';

		return $truncate;
	}
}