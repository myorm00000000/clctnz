<?php

$name = str_replace(' ', '_', $op->name);
$sql = $op->sql_text;

if (false !== strpos($sql, "\n")) {
  $sql = explode("\n", $sql);
}

if (!is_array($sql)) {
  $p = new PHPSQLParser($sql);
  if (isset($p->parsed['INSERT'])) generateFromInsert($p, $name, $sql, $op->role);
  if (isset($p->parsed['UPDATE'])) generateFromUpdate($p, $name, $sql, $op->role);
  if (isset($p->parsed['DELETE'])) generateFromDelete($p, $name, $sql, $op->role);
  if (isset($p->parsed['SELECT'])) generateFromSelect($p, $name, $sql, $op->role);
}
else {
  generateFromMulti($name, $sql, $op->role);
}
