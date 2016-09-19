<?php
/**
 * Silex ZendDbExtension tests.
 *
 * PHP version 5.3
 *
 * Copyright (c) 2011-2012 Shinya Ohyanagi, All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Shinya Ohyanagi nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @use       \Silex
 * @category  \Silex
 * @package   \Silex\Extensions
 * @version   $id$
 * @copyright (c) 2011-2012 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 */

require_once 'prepare.php';

use Silex\WebTestCase;

/**
 * Zend_Db Test.
 *
 * @use       \Silex
 * @category  \Silex
 * @package   \Silex\Extensions
 * @version   $id$
 * @copyright (c) 2011-2012 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 */
class ZendDbExtensionTest extends WebTestCase
{
    public function createApplication()
    {
        return require dirname(__DIR__) . '/examples/index.php';
    }

    private function _createDb()
    {
        $app->register(new \Zf1\DbExtension(), array(
            'zend.db.adapter' => 'Pdo_Sqlite',
            'zend.db.options' => array(
                'dbname' => __DIR__ . '/test.db',
            )
        ));

    }

    public function tearDown()
    {
        $db = $this->app['zend.db.connection'];
        $db->exec('drop table entries');
    }

    private function _client($uri, $method = 'GET')
    {
        $client = $this->createClient();
        $client->request($method, $uri);
        return $client->getResponse()->getContent();
    }

    public function testShouldReturnCreatedTableName()
    {
        $content = $this->_client('/create_table');
        $this->assertSame($content, 'entries');
    }

    public function testShouldInsertData()
    {
        $this->_client('/insert');
        $db    = $this->app['zend.db']();
        $items = $db->fetchRow('select title, text from entries order by id desc');
        $this->assertSame($items['title'], 'test_title');
        $this->assertSame($items['text'], 'test_text');
    }

    public function testShouldReturnRowsByUsingTable()
    {
        $this->_client('/insert');
        $content = $this->_client('/row');
        $item    = json_decode($content);
        $this->assertSame($item->title, 'test_title');
        $this->assertSame($item->text, 'test_text');
    }

    public function testShouldUpdateDataByUsingTable()
    {
        $this->_client('/insert');
        $this->_client('/update');
        $content = $this->_client('/row');
        $item    = json_decode($content);
        $this->assertSame($item->title, 'testtest');
        $this->assertSame($item->text, 'test_text');
    }

    public function testShouldDeleteDataByUsingTable()
    {
        $this->_client('/insert');
        $this->_client('/delete');
        $content = $this->_client('/row');
        $this->assertSame($content, '');
    }
}
