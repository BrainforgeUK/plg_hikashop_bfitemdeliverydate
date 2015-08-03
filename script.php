<?php
/**
 * @package   Handles customer item delivery date selection.
 * @version   0.0.1
 * @author    http://www.brainforge.co.uk
 * @copyright Copyright (C) 2012 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of IFrameBrain component
 */
class plgHikashopBFItemDeliveryDateInstallerScript
{
  public $mailScripts = array('order_creation_notification.html', 'order_status_notification.html', 'order_admin_notification.html');

	/**
	 * method to install custom mail scripts
	 *
	 * @return void
	 */
	private function installCustomMail($parent) 
	{
    foreach($this->mailScripts as $mailScript) {
      $source = 'html/emails/' . $mailScript . '.modified.php';
      $src = __DIR__ . '/' . $source;
      if (!JFile::exists($src)) {
        echo '<span style="color:red;"><hr />';
        echo 'Customised email file not available: ' . $source . '<br />';
        echo 'Check customisation instructions in: plugins/hikashop/html/emails<br />';
        echo '</span>';
        continue;
      }
      $target = 'media/com_hikashop/mail/' . $mailScript . '.modified.php';
      $dest = JPATH_ROOT . '/' . $target;
      if (JFile::exists($dest)) {
        if ($this->compareCustomMail($dest, $src)) continue;
        echo '<span style="color:red;"><hr />';
        echo 'Modified email file already exists: ' . $target . '<br />';
        echo 'Check customisation instructions in: plugins/hikashop/bf_item_delivery_date/html/emails<br />';
        echo '</span>';
        continue;
      }
      JFile::copy($src, $dest);
    }
	}
 
	/**
	 * method to compare custom mail scripts
	 *
	 * @return void
	 */
	private function compareCustomMail($dest, $src, $uninstall=false) 
	{
    if (!JFile::exists($dest)) return false;
    if (!JFile::exists($src)) return false;
    $sourceContent = file_get_contents($src);
    $targetContent = file_get_contents($dest);
    if (strcmp($targetContent, $sourceContent)) {
      if ($uninstall) return false;
      if (!preg_match('@// Start Added BRAINFORGE for.* bf_item_delivery_date .*plugins@', $targetContent)) return false;
      if (!preg_match('@// End Added BRAINFORGE for.* bf_item_delivery_date .*plugins@', $targetContent)) return false;
      return true;
    }
    return true;
	}
 
	/**
	 * method to uninstall custom mail scripts
	 *
	 * @return void
	 */
	private function uninstallCustomMail($parent) 
	{
    $orderPlugin = JPATH_ROOT . '/plugins/hikashop/bforderdeliverydate';
    if (JFolder::exists($orderPlugin)) return;
    foreach($this->mailScripts as $mailScript) {
      $target = 'media/com_hikashop/mail/' . $mailScript . '.modified.php';
      $dest = JPATH_ROOT . '/' . $target;
      $source = 'html/emails/' . $mailScript . '.modified.php';
      $src = __DIR__ . '/' . $source;
      if ($this->compareCustomMail($dest, $src, true)) JFile::delete($dest);
    }
	}
 
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
    $this->installCustomMail($parent);
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
    $this->uninstallCustomMail($parent);
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
    $this->installCustomMail($parent);
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}
}