<?php
namespace Home\Controller;
use Think\Controller;
class OtherexpenseController extends \Home\Controller\IndexController {
    public function index(){
    	$page_number = C('PAGE_NUMBER');
    	
    	$model_otherexpense = D('Otherexpense');
    	$array = $model_otherexpense->show_page($page_number);
    	$this->assign('str',$array["str"]);// 赋值数据集
    	$this->assign('data',$array["data"]);// 赋值分页输出
   		
    	$this->display();
    }
    
    public function printresult(){
    	
    	$model_otherexpense = D('Otherexpense');
    	
    	$this->assign("data",$model_otherexpense->show());
    
    	$this->display();
    }
    
    
	
    //添加
	public function add(){
		if(IS_POST){
			//创建模型
			$model_otherexpense = D('Otherexpense');
			if($model_otherexpense->create()){
// 				dump(I('post.'));
// 				die;
				if($model_otherexpense->add()){
					$this->success("添加成功！",U("index"));
					exit;
				}
				else{
					if(APP_DEBUG){
						$sql = $model_otherexpense->getLastSql();
						$this->error("插入数据失败，SQL：".$sql."出错原因：".mysql_error());
					}
					else
						$this->error("插入数据失败，请重试");
				}
			}
			//表单验证失败
			else{
				$error = $model_otherexpense->getError();
				$this->error(implode("<br/>",$error));
			}
		}
		
		$model_user = M('user');
		$this->assign("data_user",$model_user->field('id,username')->select());
		
		
		//显示表单
		$this->display();
	}
	
	
	//编辑
	public function edit($id){
		$model_otherexpense = M('Otherexpense');
		$model_user  	     = M('User');
		
		$data = $model_otherexpense->find($id);
		
		$this->assign("data",$data);
		$this->assign("data_user",$model_user->field('id,username')->select());
		
		
		$this->display();
	}
	
	public function update(){
		if(IS_POST){
			$model_otherexpense = D('Otherexpense');
			if($model_otherexpense->create()){
				$result = $model_otherexpense->save();
				if($result!==FALSE)
					$this->success("修改数据成功！",U('index'));
				else 
				{
					if(APP_DEBUG){
						$sql = $model_otherexpense->getLastSql();
						$this->error("插入数据失败，SQL：".$sql."出错原因：".mysql_error());
					}
					else
						$this->error("插入数据失败，请重试");
				}
			}
			else{
				//返回一个数组，拼装成字符串
				$error = $model_otherexpense->getError();
				$this->error(implode("<br/>",$error));
			}
		}
	}
	
	//删除
	public function del($id){
		$model_otherexpense = M('Otherexpense');
		
		
		//获取其他支出记录
		$data = $model_otherexpense
		->field('out_money,out_time')
		->where("id IN($id)")
		->select();

		if($data){
			$model_account = M('Account');
			
			foreach($data as $k=>$v){
				$year = substr($v['out_time'],0,4);
				$condition = "account_year={$year} AND account_type=3";
				$data_account = $model_account->where($condition)->find();
				
				if($data_account){
					$model_account
					->where($condition)
					->setField(array(
							"account_money"=>$data_account['account_money']-$v['out_money'],
					));
				}//if $data_account
			}//foreach
		}//if $data
		
		$result = $model_otherexpense->delete($id);
		if($result)
			$this->success("删除成功！",U('index'));
		else 
			$this->error("删除失败,请刷新重试！");
	}
	
	//批量删除
	public function bdel(){
		$did = I('post.delid');
		$str = implode(',', $did);
		
		//调用删除方法
		$this->del($str);
	}
}