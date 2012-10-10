<?php $this->view("header"); ?>

<h2>menu</h2>

<h3>collections</h3>
<ul>
 <li><a href="/collectible/define">Define collectible</a></li>
<?php foreach ($collectibles as $collectible): $name = strtolower(humanize($collectible)) ?>
 <li><a href="/collectible/alter/<?=$collectible?>">Alter '<?=$name?>' table</a></li>
<?php endforeach; ?>
</ul>

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

<h3>applications</h3>
<ul>
 <li>
  <a href="/application/export">Export</a> /
  <a href="/application/import">Import</a>
 </li>
 <li><a href="/application/reset">Reset</a></li>
</ul>

<?php $this->view("footer"); ?>

