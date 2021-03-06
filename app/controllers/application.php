<?php
require_once(APPPATH.'../lib/PHP-SQL-Parser/php-sql-parser.php');

function getTemplate($path, $replacement = null) {
  if (!$replacement) {
    return getFile($path);
  }
  return
    str_replace('$collectible', $replacement,
      str_replace('<?=$collectible?>', $replacement, file_get_contents(APPPATH.$path)));
}

function getFile($path) {
  return file_get_contents(APPPATH.$path);
}

class Application extends CI_Controller
{
  function __construct() {
    parent::__construct();
    $this->load->model('CollectionApp');
    $this->load->helper('controller_gen');
  }

  function export() {
    $collections = $this->CollectionApp->getTables();
    $operations = $this->CollectionApp->getOperations();
    $code = $this->generateCode($collections, $operations);
    if ($this->input->post('to_file') == true) {
      $this->downloadAsZip($code);
    }
    else {
      $this->load->view('export', array('code' => $code));
    }
  }

  private function generateController($operations) {
    $methods = array();
    foreach ($operations as $op) {
      $oop = $this->CollectionApp->getOperation($op->id);
      $methods[] = $this->load->view('templates/app/controllers/controller_fragment', array('op' => $oop), true);
    }
    return getTemplate('views/templates/app/controllers/application.php', implode("\n", $methods));
  }

  private function generateModel() {
    return getTemplate('views/templates/app/models/model.php');
  }

  private function generateCode($collectibles, $operations) {
    $this->load->library('parser');

    $code[] = array('name' => "app/config/autoload.php", 'code' => getTemplate('views/templates/app/config/autoload.php', "'Model'"));
    $parseData = array(
      'base_url' => 'http://app.local', // TODO: ask user for this value
      'encryption_key' => rand(),
      '::' => ''); // clever way to get around all the PHP config/code being elided into nothing...
    $code[] = array('name' => "app/config/config.php", 'code' => $this->parser->parse('templates/app/config/config', $parseData, true));
    $dbData = $this->CollectionApp->getDatabaseSettings();
    $parseData = array(
      'hostname' => $dbData['hostname'],
      'username' => $dbData['username'],
      'password' => $dbData['password'],
      'database' => $dbData['database'],
      '::' => '');
    $code[] = array('name' => 'app/config/database.php', 'code' => $this->parser->parse('templates/app/config/database', $parseData, true));
    $config = array();
    foreach ($operations as $op) {
      $name = str_replace(' ', '_', $op->name);
      $config[] = "  '$name' => array('field' => 'TODO', 'label' => 'TODO', 'rules' => 'TODO'),";
    }
    $code[] = array('name' => "app/config/form_validation.php", 'code' => getTemplate('views/templates/app/config/form_validation.php', join($config, "\n")));

    $code[] = array('name' => 'app/config/routes.php', 'code' => getTemplate('views/templates/app/config/routes.php'));
    $code[] = array('name' => 'app/controllers/application.php', 'code' => $this->generateController($operations));
    $code[] = array('name' => 'app/controllers/site.php', 'code' => getTemplate('views/templates/app/controllers/site.php'));
    $code[] = array('name' => 'app/models/model.php', 'code' => $this->generateModel());
    // NB: Model empty for now; putting all the DB stuff in the 'operation' in app controller

    $appData = $this->CollectionApp->getHeaderFooter();
    $code[] = array('name' => 'app/views/header.php', 'code' => $appData->header);
    $menuOperations = array();
    foreach ($operations as $op) {
      if ($op->main_menu) $menuOperations[] = $op;
    }
    $code[] = array('name' => 'app/views/menu.php', 'code' => $this->load->view('templates/app/views/menu', array('operations' => $menuOperations), true));
    $code[] = array('name' => 'app/views/footer.php', 'code' => $appData->footer);

    $sql = array("-- setup");
    foreach ($collectibles as $collectible) {
      $s = $this->CollectionApp->getSql($collectible);
      $sql[] = preg_replace("/AUTO_INCREMENT=\d+ /", "", $s);
    }
    $sql[] = "\n\n-- triggers";
    foreach ($this->db->query('show triggers')->result() as $trigger) {
      $spec = $this->db->query('show create trigger ' . $trigger->Trigger)->row();
      $osql = $spec->{'SQL Original Statement'};
      $sql[] = preg_replace("/CREATE DEFINER=.* TRIGGER/", 'CREATE TRIGGER', $osql);
    }
    $code[] = array('name' => 'sql/setup.sql', 'code' => join($sql, ";\n\n") . ';');
    $ops = array();
    foreach ($operations as $op) {
      $o = $this->CollectionApp->getOperation($op->id);
      $s = $o->sql_text;
      $ss = preg_replace("/\n/", "\\n", $s);
      $vc = preg_replace("/\n/", "\\n", $o->view_code);
      $ops[] = "/*\n-- {$op->name}\n" . $s . "\n*/";
      $ops[] = "INSERT INTO _clctnz_operations(name, role, main_menu, sql_text, has_view, view_code) VALUES('{$op->name}', '{$op->role}', $op->main_menu, '{$ss}', $op->has_view, '{$vc}');\n";

      $name = str_replace(' ', '_', $op->name);
      $code[] = array('name' => "app/views/$name.php", 'code' => ($op->has_view == 1 ? $o->view_code : "TODO: create view for operation $name"));
    }
    $code[] = array('name' => 'sql/operations.sql', 'code' => join($ops, "\n\n"));
    $code[] = array('name' => 'sql/teardown.sql', 'code' => "DROP TABLE IF EXISTS\n  " . join($collectibles, ",\n  ") . ';');

    $code[] = array('name' => 'web/index.php', 'code' => getTemplate('../web/index.php'));
    $code[] = array('name' => 'web/.htaccess', 'code' => getTemplate('../web/.htaccess'));
    $appData = $this->CollectionApp->getStyle();
    $code[] = array('name' => 'web/res/style.css', 'code' => $appData->style);

    return $code;
  }

  private function downloadAsZip($code) {
    $this->load->library('zip');

    $this->zip->read_dir(BASEPATH.'../../lib/', false);
    // want 'lib' to be in 'application/lib'
    $t = tempnam('/tmp', '_clctnz_');
    $this->zip->archive($t);
    shell_exec("mkdir -p /tmp/foo/application");
    shell_exec("unzip -d /tmp/foo $t");
    shell_exec("mv /tmp/foo/lib /tmp/foo/application");
    $this->zip->clear_data();
    $this->zip->read_dir('/tmp/foo/application/', false);
    shell_exec("rm -rf /tmp/foo");
    unlink($t);

    $copies = array(
      'config/constants.php',
      'config/doctypes.php',
      'config/foreign_chars.php',
      'config/hooks.php',
      'config/migration.php',
      'config/mimes.php',
      'config/profiler.php',
      'config/smileys.php',
      'config/user_agents.php',
      'errors/error_404.php',
      'errors/error_db.php',
      'errors/error_general.php',
      'errors/error_php.php');
    foreach ($copies as $c) {
      $this->zip->add_data('application/app/'.$c, getFile($c));
    }
    $this->zip->add_dir(array(
      'application/app/cache',
      'application/app/core',
      'application/app/helpers',
      'application/app/hooks',
      'application/app/language/english',
      'application/app/libraries',
      'application/app/logs',
      'application/app/third_party',
      'application/test',
      'application/web/res'));

    foreach ($code as $c) {
      $this->zip->add_data('application/'.$c['name'], $c['code']);
    }

    $this->zip->download('application.zip');
  }

  function reset() {
    require APPPATH.'config/database.php'; // NB: I wish CI had a way to access these in a less-hacky way
    $this->load->dbforge();

    $this->dbforge->drop_database($db['default']['database']);
    $this->dbforge->create_database($db['default']['database']);

    $this->db->close();

    $this->load->database();

    $this->dbforge->add_field('id');
    $this->dbforge->add_field(array(
      'name' => array('type' => 'varchar', 'constraint' => 255),
      'role' => array('type' => 'varchar', 'constraint' => 255),
      'main_menu' => array('type' => 'tinyint'),
      'sql_text' => array('type' => 'text'),
      'has_view' => array('type' => 'tinyint'),
      'view_code' => array('type' => 'text'),
    ));
    $this->dbforge->create_table('_clctnz_operations');

    $this->dbforge->add_field(array(
      'name' => array('type' => 'varchar', 'constraint' => 255),
      'value' => array('type' => 'text', 'null' => true),
    ));
    $this->dbforge->add_key('name', true);
    $this->dbforge->create_table('_clctnz_application');
    $this->db->query("
      INSERT INTO _clctnz_application (name)
      VALUES ('header'), ('footer'), ('style'),
             ('db_hostname'), ('db_username'), ('db_password'), ('db_database')");

    redirect('/');
  }

  function import() {
    if (!$this->form_validation->run('collectibles_import')) {
      $this->load->view('import');
    }
    else {
      $this->load->view('header');
      $this->output->append_output('<h2>import</h2>');
      $sqls = preg_split("/;\n/", $this->input->post('sql'));
      foreach ($sqls as $sql) {
        $sql = trim($sql);
        if ($sql == "") continue;

        $status = $this->db->simple_query($sql) ? '' : '<br>FAILED';
        $line = substr($sql, 0, strpos($sql, "\n"));
        $this->output->append_output("<code>$line...$status</code>");
      }
      $this->load->view('footer');
    }
  }

  function operation() {
    if (!$this->form_validation->run('operation')) {
      $this->load->view('ops');
    }
    else {
      $this->CollectionApp->saveOperation(
        $this->input->post('name'),
        $this->input->post('role'),
        $this->input->post('main_menu'),
        $this->input->post('sql_text'),
        $this->input->post('has_view'),
        $this->input->post('view_code'));
      redirect('/');
    }
  }

  function operation_alter($id) {
    if (!$this->form_validation->run('operation')) {
      $op = $this->CollectionApp->getOperation($id);
      $this->load->view('op', array('op' => $op));
    }
    else {
      $this->CollectionApp->updateOperation(
        $id,
        $this->input->post('name'),
        $this->input->post('role'),
        $this->input->post('main_menu'),
        $this->input->post('sql_text'),
        $this->input->post('has_view'),
        $this->input->post('view_code'));
      redirect('/');
    }
  }

  function operation_delete($id) {
    $this->CollectionApp->deleteOperation($id);
    redirect('/');
  }

  function database() {
    if (!$this->form_validation->run('database_settings')) {
      $dbData = $this->CollectionApp->getDatabaseSettings();
      $this->load->view('application/database_settings', $dbData);
    }
    else {
      $this->CollectionApp->updateDatabaseSettings(
        $this->input->post('hostname'),
        $this->input->post('username'),
        $this->input->post('password'),
        $this->input->post('database'));
      redirect('/');
    }
  }

  function header_footer() {
    if (!$this->form_validation->run('header_footer')) {
      $appData = $this->CollectionApp->getHeaderFooter();
      $this->load->view('application/header_footer', array('header' => $appData->header, 'footer' => $appData->footer));
    }
    else {
      $this->CollectionApp->updateHeaderFooter($this->input->post('header'), $this->input->post('footer'));
      redirect('/');
    }
  }

  function style() {
    if (!$this->form_validation->run('style')) {
      $appData = $this->CollectionApp->getStyle();
      $this->load->view('application/style', array('style' => $appData->style));
    }
    else {
      $this->CollectionApp->updateStyle($this->input->post('style'));
      redirect('/');
    }
  }
}

