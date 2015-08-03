<?php
/**
 * @package    HikaShop for Joomla!
 * @version    1.5.8
 * @author    hikashop.com
 * @copyright  (C) 2010-2012 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="hikashop_product_custom_item_info" class="hikashop_product_custom_item_info">
  <table width="100%">
  <?php
  foreach ($this->itemFields as $fieldName => $oneExtraField) {
    $itemData = JRequest :: getString('item_data_' . $fieldName, $this->element-> $fieldName);
    // Start Added BRAINFORGE 30 June 2012
    if ($fieldName == 'item_delivery_date_text' && class_exists('plgHikashopBFItemDeliveryDate')) {
      echo '<tr style="display:none;"><td></td><td>';
      echo $this->fieldsClass->display($oneExtraField,$itemData,'data[item]['.$oneExtraField->field_namekey.']',false,' ');
      echo '</td></tr>';
      continue;
    }
    // End Added BRAINFORGE 30 June 2012
    ?>
    <tr id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>" class="hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line">
      <td class="key">
        <span id="hikashop_product_custom_item_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_name">
          <?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
        </span>
      </td>
      <td>
        <span id="hikashop_product_custom_item_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_value"><?php 
          $onWhat='onchange';
          if($oneExtraField->field_type=='radio') $onWhat='onclick';
          $oneExtraField->product_id = $this->element->product_id;
          // Start Added BRAINFORGE 30 June 2012
          if ($fieldName == 'item_delivery_date' && class_exists('plgHikashopBFItemDeliveryDate')) {
            plgHikashopBFItemDeliveryDate::getDefault($this, $this->item->$fieldName);
            echo plgHikashopBFItemDeliveryDate::display($this, $fieldName, $this->item->$fieldName, null,
                                                    'style="text-align:right;"');
            echo '<div style="display:none;">';
            echo $this->fieldsClass->display($oneExtraField,$itemData,'data[item]['.$oneExtraField->field_namekey.']',false,' ');
            echo '</div>';
          }
          else {
            // End Added BRAINFORGE 30 June 2012
                      echo $this->fieldsClass->display($oneExtraField,$itemData,'data[item]['.$oneExtraField->field_namekey.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'item\',0);"');
            // Start Added BRAINFORGE 30 June 2012
          }
          // End Added BRAINFORGE 30 June 2012
        ?></span>
      </td>
    </tr>
  <?php
  }
  ?>
  </table>
</div>