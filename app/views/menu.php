<?php $this->view("header"); ?>

<h2>menu</h2>

<div class="multicol">
<h3>collections</h3>
<ul class="multicol">
 <li><a href="/collectible/define">Define collectible</a></li>
<?php foreach ($collectibles as $collectible): $name = strtolower(humanize($collectible)) ?>
 <li><a href="/collectible/alter/<?=$collectible?>">Alter '<?=$name?>'</a></li>
<?php endforeach; ?>
</ul>
</div>

<div class="multicol">
<h3>operations</h3>
<ul>
 <li><a href="/application/operation">Define operation</a></li>
<?php foreach ($operations as $op): ?>
 <li><a href="/application/operation_alter/<?=$op->id?>">Alter '<?=$op->name?>'</a> (<?=$op->role?>)</li>
<?php endforeach; ?>
</ul>
</div>

<div style="clear:both"></div>

<h3>items</h3>
<ul>
<?php foreach ($collectibles as $collectible): $name = strtolower(humanize($collectible)) ?>
 <li>
  <a href="/items/add/<?=$collectible?>">Add</a> /
  <a href="/items/all/<?=$collectible?>">view</a>
  <?=$name?>
 </li>
<?php endforeach; ?>
 <li><a href="/items">View all collectibles</a></li>
</ul>

<h3>application</h3>
<ul>
 <li>
  <a href="/application/export">Export</a> /
  <a href="/application/import">Import</a>
 </li>
 <li><a href="/application/database">Database settings</a></li>
 <li><a href="/application/header_footer">Set header/footer</a></li>
 <li><a href="/application/style">Set style</a></li>
 <li><a href="/application/reset">Reset</a></li>
</ul>

<?php $this->view("footer"); ?>

