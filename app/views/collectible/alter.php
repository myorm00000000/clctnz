<?php $this->view("header"); ?>

<h2>alter <?=$collectible?></h2>

<p>This page should allow the user to alter the description of a "collection", i.e., of a certain type.</p>

<p>The SQL to create the table '<?=$collectible?>' is currently:</p>
<pre><code><?=$sql?></code></pre>

<!--pre><?php print_r($description); ?></pre-->

<?=form_open('collectible/alter/'.$collectible)?>
 <input type="text" name="collectible_name"/>
 <input type="submit" value="Rename table"/>
</form>

<p><a href="/collectible/delete/<?=$collectible?>"/>Delete <?=$collectible?> table</a></p>

<?php $this->view("footer"); ?>

