<?php
/**
 * @package   Handles customer item delivery date selection.
 * @version   0.0.2
 * @author    http://www.brainforge.co.uk
 * @copyright Copyright (C) 2012 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgHikashopBFItemDeliveryDate extends JPlugin{
  static $_params = null;
  static $_dateformat = null;
  static $_extraDeliveryDates = null;
  static $_blockedDeliveryDates = null;
  static $_deliveryOptions = null;
  static $_deliveryDates = 0;      // Count of All delivery dates shown to user (including extras)
  static $_deliveryNmlDates = 0;   // Count of normal delivery dates shown to user
  static $_fieldRequired = 0;
  static $_resetFlag = false;
  static $_resetValue = null;
    
	public function __construct($subject, $config) {
		parent::__construct($subject, $config);

    if (empty(self::$_params)) {
    	self::$_params = $this->params;
    }
  }

  private function checkOrderDeliverydate(&$order) {
    if (JFactory::getApplication()->isAdmin()) return true;
    
    self::$_deliveryOptions = null;
    self::$_deliveryDates = 0;
    self::$_deliveryNmlDates = 0;
    self::getDeliveryDates();
    $datesValid = true;
    $item_delivery_dates = array();
    foreach ($order->cart->products as $product) {
      if (empty($product->item_delivery_date)) {
        if (empty(self::$_fieldRequired)) continue;
        JError::raiseWarning( 4720, self::$_params->get('nodeliverydate'));
        $datesValid = false;
        break;
      }

      
	    $dateValid = false;
  		foreach(self::$_deliveryOptions as $optionValue => $optionLabel) {
  		  if ($product->item_delivery_date == $optionValue) {
  		    $item_delivery_dates[$optionValue] = $optionLabel;
  		    $dateValid = true;
          break;
        }
      }
      if (!$dateValid) {
        JError::raiseWarning( 4720, sprintf(self::$_params->get('misseddeliverytime'), $product->item_delivery_date_text));
        $datesValid = false;
        break;
		  }
    }
    
    if ($datesValid && isset($optionValue) && isset($item_delivery_dates[$optionValue])) {
      if (count($item_delivery_dates[$optionValue]) == 1) {
        $fieldsClass = hikashop_get('class.field');
        $itemFields = $fieldsClass->getFields('',$element,'order');
        if (isset($itemFields['order_delivery_date']) && isset($itemFields['order_delivery_date_text'])) {
          foreach($item_delivery_dates as $optionValue => $optionLabel) {
            if (empty($order->order_delivery_date)) {
              $order->order_delivery_date = $optionValue;
              $order->order_delivery_date_text = $optionLabel;          
            }
          }          
        }
      }
      return true;
    }

    if (self::$_params->get('shownodate')) self::$_resetValue = null;
    else self::getDefault($order, self::$_resetValue);
    self::$_resetFlag = true;
    return false;
  }
  
  function onBeforeOrderCreate(&$order,&$do) {
    $value = self::checkOrderDeliveryDate($order);
    if (empty($value)) $do = false;

		$fieldsClass = hikashop_get('class.field');
    $null = null;
		$extraFields = $fieldsClass->getFields(null,$null,'order');
    if (isset($extraFields['item_delivery_date_raw'])) $order->item_delivery_date_raw = $value;
  }

  static protected function parseExtraDeliveryDates(&$matches) {
    $year = (int) $matches[1];
    if ($year < 100) $year += 2100;
    $month = (int) $matches[2];
    if ($month < 10) $month = '0' . $month;
    $day = (int) $matches[3];
    if ($day < 10) $day = '0' . $day;
    self::$_extraDeliveryDates[] = $year . '-' . $month . '-' . $day;
  }
  
  static protected function parseBlockedDeliveryDates(&$matches) {
    $year = (int) $matches[1];
    if ($year < 100) $year += 2100;
    $month = (int) $matches[2];
    if ($month < 10) $month = '0' . $month;
    $day = (int) $matches[3];
    if ($day < 10) $day = '0' . $day;
    self::$_blockedDeliveryDates[] = $year . '-' . $month . '-' . $day;
  }
  
  static private function blockedDeliveryDate($value) {
    if (self::$_blockedDeliveryDates == null) {
      self::$_blockedDeliveryDates = array();
  		preg_replace_callback('#([0-9]+)[-/]([0-9]+)[-/]([0-9]+)#', array('plgHikashopBFItemDeliveryDate', 'parseBlockedDeliveryDates'), trim(self::$_params->get('blockeddays')));
    }

    if (!empty(self::$_blockedDeliveryDates)) {
      $valueDate = date('Y-m-d', $value);
      foreach (self::$_blockedDeliveryDates as $blockedDeliveryDate) {
        if ($blockedDeliveryDate == $valueDate) return true;
      }
    }
    return false;
  }
  
  static private function extraDeliveryDate($value) {
    if (self::$_extraDeliveryDates == null) {
      self::$_extraDeliveryDates = array();
  		preg_replace_callback('#([0-9]+)[-/]([0-9]+)[-/]([0-9]+)#', array('plgHikashopBFItemDeliveryDate', 'parseExtraDeliveryDates'), trim(self::$_params->get('extradays')));
    }

    if (!empty(self::$_extraDeliveryDates)) {
      $valueDate = date('Y-m-d', $value);
      foreach (self::$_extraDeliveryDates as $extraDeliveryDate) {
        if ($extraDeliveryDate == $valueDate) return true;
      }
    }
    return false;
  }
  
  static private function checkDeliveryDate($now, $day) {
    $value = $now + ($day * 24 * 60 * 60);  // Whoops, watch out for years with leap seconds!
    switch (date('N', $value)) {
      case '1':
        $allowedDate = self::$_params->get('monday');
        break;
      case '2':
        $allowedDate = self::$_params->get('tuesday');
        break;
      case '3':
        $allowedDate = self::$_params->get('wednesday');
        break;
      case '4':
        $allowedDate = self::$_params->get('thursday');
        break;
      case '5':
        $allowedDate = self::$_params->get('friday');
        break;
      case '6':
        $allowedDate = self::$_params->get('saturday');
        break;
      case '7':
        $allowedDate = self::$_params->get('sunday');
        break;
      default:
        return false;
    }
    
    if (self::blockedDeliveryDate($value)) return false;

    if ($allowedDate) self::$_deliveryNmlDates += 1;
    else if (!self::extraDeliveryDate($value)) return false;

    return $value;
  }
  
  static private function addDeliveryDate($now, $day) {
    $value = self::checkDeliveryDate($now, $day);
    if ($value) {
      self::$_deliveryDates += 1;
      $key = date('Ymd', $value) . '@' . $day . '@' . self::$_deliveryDates . '@' . (self::$_deliveryNmlDates-1);
      if (empty(self::$_dateformat)) self::$_dateformat = self::$_params->get('dateformat');
      self::$_deliveryOptions[$key] = date(self::$_dateformat, $value);
      return true;
    }
    return false;
  }
  
  static private function getDeliveryDates() {
    if (!empty(self::$_deliveryOptions)) return;

    self::$_deliveryOptions = array();
    self::$_deliveryDates = 0;
    self::$_fieldRequired = 0;
    if (self::$_params->get('shownodate')) {
      $fieldsClass = hikashop_get('class.field');
      $itemFields = $fieldsClass->getFields('',$element,'item');
      foreach($itemFields as $itemField) self::$_fieldRequired += $itemField->field_required;
      if (self::$_fieldRequired) self::$_deliveryOptions[''] = self::$_params->get('selectdate');
      else self::$_deliveryOptions[''] = null;
    }

    $maxdaysinadvance = self::$_params->get('maxdaysinadvance');
    if (empty($maxdaysinadvance)) return;
    $now = time();
    $nowHours = (int)date('H', $now);
    $nowMins = (int)date('i', $now);
    $minadvancedays = self::$_params->get('minadvancedays');
    $maxdaysinadvance -= $minadvancedays;

    // Check if we can order on current day
    $day = 0;
    if (self::checkDeliveryDate($now, $day)) {
      // If too late to order today skip to next available date    
      $lastestordertime = self::$_params->get('lastestordertime');
      if (!empty($lastestordertime)) {
        $latestorder = explode(':', $lastestordertime);
        if (count($latestorder) != 2 || !preg_match('/^[0-9]+:[0-9]+$/', $lastestordertime)) {
          JError::raiseWarning( 4720, 'Error (Last Order Time): Please contact System Administrator' );
          return false;
        }
        if (((int)$latestorder[0] <= $nowHours) ||
            ((int)$latestorder[0] == $nowHours && (int)$latestorder[1] <= $nowMins)) {
          $day += 1; 
          $maxdaysinadvance -= 1;
        }
      }
    }

    // Check if we can order on current day, if not find the next day when we can order
    while (!self::checkDeliveryDate($now, $day)) {
      $day += 1;
      $maxdaysinadvance -= 1;
      $checkOrderTime = false;
    }
    $day += $minadvancedays + 1;

    while ($maxdaysinadvance > 0) {
      self::addDeliveryDate($now, $day);
      $maxdaysinadvance -= 1;
      $day += 1;
    }
  }
  
  static function display(&$order, $fieldName, &$value, $map=null, $options=null) {
    self::getDeliveryDates();
 		$string = '<select id="' . $fieldName . '_raw" ' . ((empty($map)) ? null : 'name="'.$map.'" ') . $options.
                       ' onChange="bfitemdeliverydateChange(this);"' .
              '>';
		if(!empty(self::$_deliveryOptions)) {
      if (self::$_resetFlag) $value = self::$_resetValue;
      $dateValue = substr($value, 0, 8); 
  		foreach(self::$_deliveryOptions as $optionValue => $optionLabel){
  			$selected = (substr($optionValue, 0, 8) == $dateValue) ? 'selected="selected" ' : '';
  			$string .= '<option value="'.$optionValue.'" id="'.$fieldName.'_'.$optionValue.'" '.$selected.'>'.$optionLabel;
        if (!empty($optionValue)) $string .= '&nbsp;&nbsp';
        $string .= '</option>';
  		}
		}
		$string .= '</select>';

    $js = '
function bfitemdeliverydateChange(el) {
  iddel = document.getElementById("' . $fieldName . '");
  if (iddel == null) return;
  iddelLabel = document.getElementById("' . $fieldName . '_text");
  if (iddelLabel == null) return;

  iddel.value = el.value;
  if (el.value == "") valLabel = "";
  else valLabel = el[el.selectedIndex].innerHTML;
  iddelLabel.value = valLabel;
}

function bfitemdeliverydateInit() {
  elraw = document.getElementById("' . $fieldName . '_raw");
  if (elraw != null) {
    document.getElementById("' . $fieldName . '").value = elraw.value;
    document.getElementById("' . $fieldName . '_text").value = elraw[elraw.selectedIndex].innerHTML;
  }
}

';

    switch (self::$_params->get('onloadinit')) {
      case 1:
        $js .= '
$(document).ready(function() {
  bfitemdeliverydateInit();
});
';
        break;
      case 2:
        $js .= '
if (window.addEventListener) // W3C standard
{
  window.addEventListener(\'load\', bfitemdeliverydateInit, false);
} 
else if (window.attachEvent) // Microsoft
{
  window.attachEvent(\'onload\', bfitemdeliverydateInit);
}
';
        break;
      default:
        $js .= '
window.addEvent("domready", function() {
  bfitemdeliverydateInit();
});
';
        break;
    }
    JFactory::getDocument()->addScriptDeclaration($js);

    return $string;
  }

  static function getDefault(&$order, &$value) {
    if (!empty($value)) return;
    self::getDeliveryDates();
		if(empty(self::$_deliveryOptions)) return;
    if(empty(self::$_fieldRequired)) return;

    $defaultdaysdelay = self::$_params->get('defaultdaysdelay');
		foreach(self::$_deliveryOptions as $optionValue => $optionLabel) {
		  if (!empty($optionValue)) {
		    if ($defaultdaysdelay <= 0) {
		      $value = $optionValue;
          return;
        }
        $defaultdaysdelay -= 1;
		  }
	  }
  }

	function onBeforeCartUpdate($hikaCart, $cart, $product_id, $quantity, $add, $type, $resetCartWhenUpdate, $force, &$do) {
    if ($quantity && $add) {
			$formData = JRequest::getVar( 'data', array(), '', 'array' );
			if(empty($formData['item'])){
    		$cart_id = $hikaCart->cart_type.'_id';
        $cartContent = $hikaCart->get($hikaCart->$cart_id,false,$hikaCart->cart_type);
        $found = false; 
        if (!empty($cartContent)) {
          $item_delivery_date = '99999999'; 
          $item_delivery_date_text = '';
          foreach ($cartContent as $item) {
            if ($item->product_id == $product_id) {
              if (substr($item->item_delivery_date, 0, 8) < substr($item_delivery_date, 0, 8)) {
                $item_delivery_date = $item->item_delivery_date; 
                $item_delivery_date_text = $item->item_delivery_date_text;
                $found = true; 
              }
            }
          }
          if (!$found) {
            foreach ($cartContent as $item) {
              if (substr($item->item_delivery_date, 0, 8) < substr($item_delivery_date, 0, 8)) {
                $item_delivery_date = $item->item_delivery_date; 
                $item_delivery_date_text = $item->item_delivery_date_text;
                $found = true; 
              }
            }
          }
        }
        if (!$found && $force) {
          $order = null;
          $value = null;
          plgHikashopBFItemDeliveryDate::getDefault($order, $value);
          if (!empty($value)) {
            $item_delivery_date = $value;
            $item_delivery_date_text = plgHikashopBFItemDeliveryDate::$_deliveryOptions[$value];
            $found = true; 
          }
        }
        if ($found) {
          $formData = array();
          $formData['item'] = array();
          $formData['item']['item_delivery_date'] = $item_delivery_date;
          $formData['item']['item_delivery_date_text'] = $item_delivery_date_text;
		      JRequest::setVar( 'data', $formData );
        }
      }
  		$fieldClass = hikashop_get('class.field');
  		$element = null;
  		$element->product_id = $product_id;
  		$data = $fieldClass->getInput('item',$element);
      if (empty($data)) $do = false;
    }
	}
}
