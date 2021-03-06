<?php
namespace Home\Model;
use Think\Model;
class OtherexpenseModel extends Model 
{
	protected $patchValidate = true;
	
	protected $_validate = array(
					array('summary', 'require', '摘要不能为空！', 1),
					array('out_money', 'require', '收入金额不能为空！', 1),
					array('out_time', 'require', '收入时间不能为空！', 1),
					array('user_id', 'chkfunc', '未选择入账人！', 3,"callback"),
					
					array('out_money', 'chkmoney', '合同价格格式不正确！', 3,"callback",3)
	); 
	
	//检测金额格式是否正确
	public function chkmoney($v){
		$subject = $v;
		$pattern = '/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/';
		$res = preg_match($pattern, $subject);
	
		if($res)
			return true;
		return false;
	}
	
	public function chkfunc($value){
		//1.出账人未选择
		if($value=="0" || in_array("0",$value) || in_array("", $value))
			return false;
		else return true;
	}
	
	
	//钩子函数
	//插入【其他支出】记录后，将其加到记账表上
	//0：工程收入1：其他收入2：工程支出3：其他支出
	public function _after_insert(&$data, $options){
		
		
		afterInsert($data, 'out_time','out_money',3);
		
		/*
		$year = substr($data['out_time'],0,3);
		
		$model_account = M('account');
		
		$condition = "account_year={$year} AND account_type=3";
		
		$result = $model_account->where($condition)->find();
		
		//记账记录不存在
		if(!$result){
			//添加记录
			$model_account
			->where($condition)
			->add(array(
					'account_year'  => $year,
					'account_type'  => 3,
					"account_money" => $data['out_money'],
					));
		}
		else{
			//修改记录
			$model_account
			->where($condition)
			->setField(array(
					"account_money"=>$result['account_money']+$data['out_money'],
					));
		}
		*/
	}
	
	public function _after_update(&$data, $options){
		
		afterUpdate(I('post.'), 'out_time', 'out_time_old', 'out_money', 'out_money_old', 3);
		
		/*
		$year     = substr($data['out_time'],0,3);
		$year_old = substr($data['out_time_old'],0,3);
		
		$out_money     = $data['out_money'];
		$out_money_old = $data['out_money_old'];
		
		$model_account = M('account');
		
		$result = $model_account
		->field('account_year,account_money')
		->where("account_type=3 AND account_year={$year}")
		->find();
		
		$result_old = $model_account
		->field('account_year,account_money')
		->where("account_type=3 AND account_year={$year_old}")
		->find();
		
		
		if( ($year==$year_old) && ($out_money!=$out_money_old) ){
			//编辑此条记录即可
			$model_account
			->where("account_year={$year} AND account_type=3")
			->setField(array(
					"account_money"=>$result['account_money']+$out_money-$out_money_old,
			));
		}
		else if($year!=$year_old){
			if($result){
				//编辑操作
				$model_account
				->where("account_year={$year} AND account_type=3")
				->setField(array(
						"account_money"=>$result['account_money']+$out_money,
				));
			}
			else{
				//添加操作
				$model_account
				->where("account_year={$year} AND account_type=3")
				->add(array(
						'account_year'  => $year,
						'account_type'  => 3,
						"account_money" => $out_money,
				));
			}//else
				
			//编辑原纪录
			$model_account
			->where("account_year={$year_old} AND account_type=3")
			->setField(array(
					"account_money"=>$result_old['account_money']-$out_money_old,
			));
		}//else if
		*/
	}
	
	//不分页显示
	public function show(){
		//查询条件
		$map = 1;
	
		//摘要
		if($summary = I('get.summary')){
			$map .= " AND summary LIKE '%{$summary}%'";
		}
	
		//时间段
		if($end = I('get.end')){
			$start = I('get.start');
	
			if(empty($start))
				$map .= " AND UNIX_TIMESTAMP('{$end}')>=UNIX_TIMESTAMP(out_time)";
			else
				$map .= " AND UNIX_TIMESTAMP('{$start}')<=UNIX_TIMESTAMP(out_time) AND UNIX_TIMESTAMP('{$end}')>=UNIX_TIMESTAMP(out_time)";
		}
	
		//支出金额
		$min = intval(I('get.min'));
		$max = intval(I('get.max'));
	
		if($min>0){
			if($max==0)
				$map .= " AND out_money>={$min}";
			else if($min<$max)
				$map .= " AND out_money>={$min} AND out_money<={$max}";
		}
		else if($max>0)
			$map .= " AND out_money<={$max}";

		//数据查询
		$data = $this
		->where($map)
		->select();
	
// 		 		var_dump($this->getLastSql());
	
		return $data;
	}
	
	

	//分页显示
	public function show_page($perpage){
		//查询条件
		$map = 1;
		
		//摘要
		if($summary = I('get.summary')){
			$map .= " AND summary LIKE '%{$summary}%'";
		}
		
		//时间段
		if($end = I('get.end')){
			$start = I('get.start');
		
			if(empty($start))
				$map .= " AND UNIX_TIMESTAMP('{$end}')>=UNIX_TIMESTAMP(out_time)";
			else
				$map .= " AND UNIX_TIMESTAMP('{$start}')<=UNIX_TIMESTAMP(out_time) AND UNIX_TIMESTAMP('{$end}')>=UNIX_TIMESTAMP(out_time)";
		}
		
		//支出金额
		$min = intval(I('get.min'));
		$max = intval(I('get.max'));
		
		if($min>0){
			if($max==0)
				$map .= " AND out_money>={$min}";
			else if($min<$max)
				$map .= " AND out_money>={$min} AND out_money<={$max}";
		}
		else if($max>0)
			$map .= " AND out_money<={$max}";
		
		$totalRows = $this->where($map)->count();
		
		$page = new \Think\Page($totalRows,$perpage);
		
		$page->setConfig("prev", "上一页");
		$page->setConfig("next", "下一页");
		$page->setConfig("first", "首页");
		$page->setConfig("last", "末页");
		
		//分页显示输出
		$str = $page->show();
		
		//分页数据查询
		$data = $this
		->alias('a')
		->field('a.*,b.username')
		->where($map)
		->join('wh_user b ON b.id=a.user_id')
		->limit($page->firstRow.','.$page->listRows)
		->select();
	
//  		var_dump($this->getLastSql());
		
		return array(
				"data" => $data,
				"str"  => $str
				);
	}
}