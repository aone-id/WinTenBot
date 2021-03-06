<?php

namespace WinTenDev\Model;

use Medoo\Medoo;
use WinTenDev\Utils\Caches;

class MalFiles
{
	static private $table_name = 'anti_malfiles';
	
	/**
	 * @param $datas
	 * @return bool|\PDOStatement
	 */
	public static function addFile($datas)
	{
		$db = new Medoo(db_data);
		$q = false;
		$table_name = 'anti_malfiles';
		$p = $db->count($table_name, ['file_id' => $datas['file_id']]);
		if ($p <= 0) {
			$db->insert($table_name, $datas);
			$q = true;
		}
		
		return $q;
	}
	
	/**
	 * @param $where
	 * @return bool|\PDOStatement
	 */
	public static function deleteFile($where)
	{
		$db = new Medoo(db_data);
		return $db->delete(self::$table_name, $where);
	}
	
	/**
	 * @param $file_id
	 * @return bool
	 */
	public static function isMalFile($file_id): bool
	{
		$result = false;
		$datas = self::readCache();
		if(!is_array($datas)) {
			return false;
		}
		
		if (count($datas) > 0) {
			foreach ($datas as $e) {
				if ($file_id == $e['file_id']) {
					$result = true;
				}
			}
		}
		return $result;
	}
	
	/**
	 * @return bool|int
	 */
	public static function writeCache()
	{
		$datas = self::getAll();
		$cache = new Caches();
		return $cache->writeCache('cache-json', 'anti-malfiles', $datas);
	}
	
	public static function readCache()
	{
		$cache = new Caches();
		return $cache->readCache('cache-json', 'anti-malfiles');
	}
	
	/**
	 * @return array|bool
	 */
	public static function getAll()
	{
		$db = new Medoo(db_data);
		return $db->select(self::$table_name, '*');
	}
}
