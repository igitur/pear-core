--TEST--
PEAR_Registry->addPackage2() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php
error_reporting(E_ALL);
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setSummary('sum');
$pf->setDescription('desc');
$pf->setLicense('PHP License');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setDate('2004-11-17');
$pf->setNotes('sum');
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$ret = $reg->addPackage2($pf);
$phpunit->assertErrors(array(
array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing Package Name'),
), 'pf1 validation errors');
$phpunit->assertFalse($ret, 'install of invalid package');
$pf->setPackage('foop');
$ret = $reg->addPackage2($pf);
$phpunit->assertTrue($ret, 'install of valid package');
$phpunit->assertNoErrors('install of valid package');
$phpunit->assertFileExists($statedir  . DIRECTORY_SEPARATOR . 'php' .
    DIRECTORY_SEPARATOR . '.registry' . DIRECTORY_SEPARATOR . 'foop.reg', 'reg file of foop.reg');
$contents = unserialize(implode('', file($statedir  . DIRECTORY_SEPARATOR . 'php' .
    DIRECTORY_SEPARATOR . '.registry' . DIRECTORY_SEPARATOR . 'foop.reg')));
$phpunit->showall();
$phpunit->assertTrue(isset($contents['_lastmodified']), '_lastmodified not set pf1');
unset($contents['_lastmodified']);
$phpunit->assertEquals($pf->getArray(), $contents, 'pf1 file saved');

$pf2 = new PEAR_PackageFile_v2;
$pf2->setConfig($config);
$pf2->setPackageType('extsrc');
$pf2->addBinarypackage('foo_win');
$pf2->setPackage('foo');
$pf2->setChannel('grob');
$pf2->setAPIStability('stable');
$pf2->setReleaseStability('stable');
$pf2->setAPIVersion('1.0.0');
$pf2->setReleaseVersion('1.0.0');
$pf2->setDate('2004-11-12');
$pf2->setDescription('foo source');
$pf2->setSummary('foo');
$pf2->setLicense('PHP License');
$pf2->setLogger($fakelog);
$pf2->clearContents();
$pf2->addFile('', 'foo.grop', array('role' => 'src'));
$pf2->addBinarypackage('foo_linux');
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf2->setNotes('blah');
$pf2->setPearinstallerDep('1.4.0a1');
$pf2->setPhpDep('4.2.0', '5.0.0');

$ret = $reg->addPackage2($pf2);
$phpunit->assertErrors(array(
array('package' => 'PEAR_PackageFile_v2', 'message' => '<extsrcrelease> packages must use <providesextension> to indicate which PHP extension is provided')
), 'invalid pf2');
$phpunit->assertFalse($ret, 'invalid pf2');

$pf2->setProvidesExtension('foo');
$cf = new PEAR_ChannelFile;
$cf->setName('grob');
$cf->setServer('grob');
$cf->setSummary('grob');
$cf->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($cf);
$phpunit->assertNoErrors('channel add');

$ret = $reg->addPackage2($pf2);
$phpunit->assertTrue($ret, 'valid pf2');
$pf2file = $statedir  . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.registry' .
    DIRECTORY_SEPARATOR . '.channel.grob' . DIRECTORY_SEPARATOR . 'foo.reg';
$phpunit->assertFileExists($pf2file, 'reg file of foop.reg');
$contents = unserialize(implode('', file($pf2file)));
$phpunit->showall();
$phpunit->assertTrue(isset($contents['_lastmodified']), '_lastmodified not set pf2');
unset($contents['_lastmodified']);
$phpunit->assertEquals($pf2->getArray(true), $contents, 'pf2 file saved');

echo 'tests done';
?>
--EXPECT--
tests done