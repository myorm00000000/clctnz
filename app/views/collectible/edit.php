<?php $this->view("header"); ?>

<h2>edit <?=$collectible?></h2>

<div class="help">
 <p>This page allows you to edit an existing collectible.</p>
</div>

<?=form_open("collectible/edit/$collectible/{$item->id}")?>

<?=validation_errors()?>

<?php foreach ($fields as $field): if ($field->name != 'id'): ?>
<p>
 <label><?=preg_replace('/_/', ' ', $field->name)?></label><br/>
<?php if ($field->type != 'text'): ?>
 <input type="text" name="<?=$field->name?>" <?=$field->type == 'int' ? 'size="9"' : '';?> value="<?=form_prep($item->{$field->name})?>"/>
 <?php
 if (array_key_exists($field->name, $refs)) {
   echo '<div class="ref"><label>' . $refs[$field->name] . '</label>';
   $rawData = $this->db->get($refs[$field->name])->result_array();
   $this->load->view('collectible/_all', array('hide_header_row' => true, 'data' => $rawData));
   echo '</div>';
 }
 ?>
<?php else: ?>
 <textarea name="<?=$field->name?>" rows="3" cols="35"><?=form_prep($item->{$field->name})?></textarea>
<?php endif; ?>
</p>
<?php endif; endforeach; ?>

<input type="submit" value="update"/>

</form>

<?php $this->view("footer"); ?>

