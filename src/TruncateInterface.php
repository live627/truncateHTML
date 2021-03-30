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
 * TruncateInterface
 *
 * @package   org.urodoz.truncatehtml
 * @author    Albert Lacarta <urodoz@gmail.com>
 * @license   http://www.opensource.org/licenses/MIT The MIT License
 */
interface TruncateInterface
{
	/**
	 * Truncates the HTML keeping consistency on open/closing HTML tags
	 *
	 * @param string $text   String to truncate
	 * @param int    $length Length of returned string, including ellipsis
	 * @param string $append Will be appended to the trimmed string
	 *
	 * @return string The truncated string
	 */
	public function truncate($text, $length = 100, $append = '…');
}