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
<?php
  $type = $this->type;
  foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
    // Start Added BRAINFORGE 30 June 2012
    if ($fieldName == 'order_delivery_date_text' && class_exists('plgHikashopBFOrderDeliveryDate')) {
      echo '<tr style="display:none;"><td></td><td>';
      echo $this->fieldsClass->display($oneExtraField,$this->$type->$fieldName,'data['.$type.']['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\''.$type.'\',0);"');
      echo '</td></tr>';
      continue;
    }
    // End Added BRAINFORGE 30 June 2012
  ?>
    <tr class="hikashop_checkout_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type.'_'.$oneExtraField->field_namekey; ?>">
      <td class="key">
        <?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
      </td>
      <td>
        <?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
          // Start Added BRAINFORGE 30 June 2012
          if ($fieldName == 'order_delivery_date' && class_exists('plgHikashopBFOrderDeliveryDate')) {
            plgHikashopBFOrderDeliveryDate::getDefault($this, $this->$type->$fieldName);
            echo plgHikashopBFOrderDeliveryDate::display($this, $fieldName, $this->$type->$fieldName, null,
                                                    'style="text-align:right;"');
            echo '<div style="display:none;">';
            echo $this->fieldsClass->display($oneExtraField,$this->$type->$fieldName,'data['.$type.']['.$fieldName.']',false,' ');
            echo '</div>';
          }
          else {
           // End Added BRAINFORGE 30 June 2012
          echo $this->fieldsClass->display($oneExtraField,$this->$type->$fieldName,'data['.$type.']['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\''.$type.'\',0);"');
          // Start Added BRAINFORGE 30 June 2012
          }
          // End Added BRAINFORGE 30 June 2012
        ?>
       </td>
    </tr>
  <?php }  ?>