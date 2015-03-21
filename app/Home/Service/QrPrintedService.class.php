<?php
namespace Home\Service;
use Think\Model;
use Think\Controller;

class QrPrintedService extends Model {
	/**
	 * 查询印刷批次数据列表
	 * @param $codeName 批次代号
	 * @author 李展威
	 */
	public function getPrintedDataList($page = 0, $num = 0) {
		if ($num == 0) {
			$result = D('QrPrinted')->where($data)->order("id desc")->select();
		} else {
			$result = D('QrPrinted')->where($data)->order("id desc")->limit($page, $num)->select();
		}
		return $result;
	}

	/**
	 * 根据类型获取印刷批次列表
	 * @param $codeName 批次代号
	 * @author 李展威
	 */
	public function getPrintedDataListByType($type, $page = 0, $num = 0) {
		$data = array("type" => $type);
		if ($num == 0) {
			$result = D('QrPrinted')->where($data)->order("id desc")->select();
		} else {
			$result = D('QrPrinted')->where($data)->order("id desc")->limit($page, $num)->select();
		}
		return $result;
	}

	/**
	 * 根据印刷批次代号查询批次
	 * @param $codeName 批次代号
	 * @author 李展威
	 */
	public function getPrintedDataByCodeName($codeName) {
		$data = array("code_name" => $codeName);
		$result = D('QrPrinted')->where($data)->find();
		return $result;
	}

	/**
	 * 根据印刷批次ID查询批次
	 * @param $printedId 批次ID
	 * @author 李展威
	 */
	public function getPrintedDataById($printedId) {
		$data = array("id" => $printedId);

		$result = D('QrPrinted')->where($data)->find();
		return $result;
	}

	/**
	 * 添加印刷批次数据
	 * @param $type 批次类型，失物贴: lost_card, 留声贴: video-card
	 * @param $codeName 批次代号
	 * @param $status 批次状态，是否已经印刷
	 * @author 李展威
	 */
	public function createPrintedData($type, $codeName, $status) {
		$data = array(
			"type" => $type,
			"code_name" => $codeName,
			"ctime" => date("Y-m-d H:i:s", time()),
			"status" => $status);

		$id = D('QrPrinted')->add($data);

		if ($id) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 更新印刷批次数据
	 * @param $data 批次数据
	 * @author 李展威
	 */
	public function updatePrintedData($data) {
		$data = array(
			"id" => $data['id'],
			"type" => $data['type'],
			"code_name" => $data['code_name'],
			"ctime" => date("Y-m-d H:i:s", time()),
			"status" => $data['status']);

		$result = D('QrPrinted')->where($data)->save();
		if (false !== $result) {
			return true;
		} else {
			return false;
		}
	}
}