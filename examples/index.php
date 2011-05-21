<?php
require_once 'silex.phar';
require_once dirname(__DIR__) . '/src/Zf1/DbExtension.php';

$zendpath = getenv('ZF_PATH');

$app = new Silex\Application();
$app->register(new \Zf1\DbExtension(), array(
    'zend.class_path' => $zendpath,
    'zend.db.adapter' => 'Pdo_Sqlite',
    'zend.db.options' => array(
        'dbname' => __DIR__ . '/test.db',
    )
));

$app->before(function() use ($app) {
    $sql   = file_get_contents(__DIR__ . '/table.sql');
    $db    = $app['zend.db']();
    $conn  = $db->getConnection();
    $app['zend.db.connection'] = $conn;
    $conn->exec($sql);

    $app['autoloader']->registerNamespace('Model', __DIR__);
});

$app->get('/create_table', function () use ($app) {
    $conn  = $app['zend.db.connection'];
    $db    = $app['zend.db']();
    $table = $db->listTables();

    return $table[0];
});

$app->get('/insert', function () use ($app) {
    $db = $app['zend.db']();
    $db->insert(
        'entries', array('title' => 'test_title', 'text' => 'test_text')
    );

    return true;
});

$app->get('/fetch', function () use ($app) {
    $db    = $app['zend.db']();
    $items = $db->fetchRow('select title, text from entries order by id desc');
    return $items;
});

$app->get('/row', function () use ($app) {
    $table = new \Model\Entries();
    $row  = $table->fetchRow();

    return ($row === null) ? '' : json_encode($row->toArray());
});

$app->get('/update', function () use ($app) {
    $db    = $app['zend.db']();
    $table = new \Model\Entries();
    $row   = $table->fetchRow($table->select()->where('id = ?', 1));
    $row->title = 'testtest';
    $row->save();
    $row = $table->fetchRow();

    return json_encode($row->toArray());
});

$app->get('/delete', function () use ($app) {
    $db    = $app['zend.db']();
    $table = new \Model\Entries();
    $row   = $table->fetchRow($table->select()->where('id = ?', 1));
    $row->delete();

    return true;
});

if (getenv('SILEX_TEST')) {
    return $app;
}
$app->run();
