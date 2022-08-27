<?php

/**
 * Bhavik Tanna
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category    Bhavik
 * @package     Bhavik_Bluedart
 * @copyright   Copyright (c) 2022 Bhavik Tanna
 * @license    http://www.opensource.org/licenses/gpl-license.php  GNU General Public License
 */

class DebugSoapClient extends SoapClient
{
	public $sendRequest = true;
	public $printRequest = false;
	public $formatXML = false;

	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		if ($this->printRequest) {
			if (!$this->formatXML) {
				$out = $request;
			} else {
				$doc = new DOMDocument;
				$doc->preserveWhiteSpace = false;
				$doc->loadxml($request);
				$doc->formatOutput = true;
				$out = $doc->savexml();
			}
			echo $out;
		}

		if ($this->sendRequest) {
			return parent::__doRequest($request, $location, $action, $version, $one_way);
		} else {
			return '';
		}
	}
}
