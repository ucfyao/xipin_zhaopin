<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class resume_controller extends user{
	
	function index_action(){
		$this->public_action();
		$this->member_satic();
		$num=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
		$maxnum=$this->config['user_number']-$num;
		if($maxnum<0){$maxnum='0';}
		$this->yunset("maxnum",$maxnum);
		$this->yunset("confignum",$this->config['user_number']);
		$rows=$this->obj->DB_select_all("resume_expect","`uid`='".$this->uid."' order by defaults desc,lastupdate desc","id,name,lastupdate,doc,tmpid,integrity,hits,statusbody,`topdate`,`top`,`r_status`,`status`");
		if($rows&&is_array($rows)){
			foreach($rows as $key=>$val){
				if($val['topdate']>1){
					$rows[$key]['topdate']=date("Y-m-d",$val['topdate']);
					$rows[$key]['topdatetime']=$val['topdate']-time();
				}else{
					$rows[$key]['topdate']='未设置';
				}
			}
		}
		
		$row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`def_job`,`idcard_status`,`idcard_pic`");
		$isallow_addresume="0";
		if($this->config['user_enforce_identitycert']=="1"){
			if($row['idcard_status']=="1"&&$row['idcard_pic']){
				$isallow_addresume="1";
			}else{
				$isallow_addresume="0";
			}
		}else{
			$isallow_addresume="1";
		}
		$this->yunset("isallow_addresume",$isallow_addresume);
		
		$this->yunset("rows",$rows);
		$resume=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."' and defaults=1","`integrity`");
		$resumepm=$this->obj->DB_select_num("resume_expect","`integrity`>".$resume['integrity']);
		$this->yunset("resumepm",$resumepm);
		
		$CacheArr=$this->MODEL('cache')->GetCache(array('user'));
		$edu=$this->obj->DB_select_all("resume_edu","`uid`='".$this->uid."' and `eid`='".$row['def_job']."' order by `sdate` desc");	
		$gdeu=0;
		foreach ($edu as $v){
			if (in_array($CacheArr['userclass_name'][$v['education']],array('本科','硕士','研究生','硕士研究生','MBA','博士研究生','博士','博士后'))){
				$gdeu=1;
			}
		}
		if($gdeu==1){
			$this->yunset('heightone',1);
		}
		$work=$this->obj->DB_select_all("resume_work","`uid`='".$this->uid."' and `eid`='".$row['def_job']."' order by `sdate` desc");
		if(is_array($work)){
			$whour = 0;$hour=array();
			foreach($work as $value){
				if ($value['edate']){
					$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
				}else{
					$workTime = ceil((time()-$value['sdate'])/(30*86400));
				}
				$hour[] = $workTime;
				$whour += $workTime;
			}
			$worknum = count($hour);
		}
		if($whour>24 || $worknum>3){
			$this->yunset('heighttwo',2);
		}	
		$this->yunset("def_job",$row['def_job']);
		$this->user_tpl('resume');
	}
	function del_action(){
		$del=(int)$_GET['id'];
		$show=$this->obj->DB_select_all("resume_show","`eid`='".$del."' and `picurl`<>''","`picurl`");
		if(is_array($show)){
			foreach($show as $v){
				unlink_pic(".".$show['picurl']);
			}
		}
		$del_array=array("resume_cert","resume_edu","resume_other","resume_project","resume_skill","resume_training","resume_work","resume_doc","user_resume","resume_show","down_resume","userid_job","user_entrust","user_entrust_record");
		if($this->obj->DB_delete_all("resume_expect","`id`='".$del."' and `uid`='".$this->uid."'")){
			foreach($del_array as $v){
				$this->obj->DB_delete_all($v,"`eid`='".$del."' and `uid`='".$this->uid."'","");
			}
			$this->obj->DB_delete_all("look_resume","`resume_id`='".$del."'","");
			$this->obj->DB_delete_all("look_job","`uid`='".$this->uid."'","");
			$this->obj->DB_delete_all("atn","`uid`='".$this->uid."'","");
			$this->obj->DB_delete_all("userid_msg","`uid`='".$this->uid."'","");
			$def_id=$this->obj->DB_select_once("resume","`uid`='".$this->uid."' and `def_job`='".$del."'");
			if(is_array($def_id)){
				$row=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."'","`id`");
				if($row['id']!=''){
				    $this->obj->update_once('resume_expect',array('defaults'=>1),array('id'=>$row['id']));
				    $this->obj->update_once('resume',array('def_job'=>$row['id'],'lastupdate'=>time()),array('uid'=>$this->uid));
				}
			}
			$num=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
			$resume_num=$num+1;
			$this->obj->DB_update_all('member_statis',"`resume_num`='".$resume_num."'","`uid`='".$this->uid."'");
			$this->obj->DB_delete_all("resume_cityclass","`eid`='".$del."'","");
			$this->obj->DB_delete_all("resume_jobclass","`eid`='".$del."'","");
			$this->layer_msg('删除成功！',9,0,"index.php?c=resume");
		}else{
			$this->layer_msg('删除失败！',8,0,"index.php?c=resume");
		}
	}
	function publicdel_action(){
		if($_POST['id']&&$_POST['table']){
			$tables=array("skill","work","project","edu","training","cert","other");
			if(in_array($_POST['table'],$tables)){
				$table = "resume_".$_POST['table'];
				$eid=(int)$_POST['eid'];
				$id=(int)$_POST['id'];
				$url = $_POST['table'];
				$nid=$this->obj->DB_delete_all($table,"`id`='".$id."' and `uid`='".$this->uid."'");
				
				$this->obj->DB_update_all("user_resume","`".$url."`=`".$url."`-1","`eid`='".$eid."' and  `uid`='".$this->uid."'");
				$resume=$this->obj->DB_select_once("user_resume","`eid`='".$eid."'");
				$resume[$url];
				if($nid){
					if($table=='resume_work'){
						
						$workList = $this->obj->DB_select_all("resume_work","`eid`='".$eid."' AND `uid`='".$this->uid."'","`sdate`,`edate`");
						$whour = 0;$hour=array();
						foreach($workList as $value){
							
							if ($value['edate']){
								$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
							}else{
								$workTime = ceil((time()-$value['sdate'])/(30*86400));
							}
							$hour[] = $workTime;
							$whour += $workTime;
						}
						
						$avghour = ceil($whour/count($hour));
						
						$this->obj->DB_update_all("resume_expect","`whour`='".$whour."',`avghour`='".$avghour."'","`id`='".$eid."' AND `uid`='".$this->uid."'");
					}
					$resume_row=$this->obj->DB_select_once("user_resume","`eid`='".$eid."'");
					$numresume=$this->MODEL('resume')->complete($resume_row);
					$data['integrity']=$numresume;
					$data['num']=$resume[$url];
					echo json_encode($data);die;
				}else{
					echo 0;die;
				}
			}else{
				echo 0;die;
			}
		}
	}
	function resumeOrder_action(){
		if($_POST){
  			$M=$this->MODEL('userpay');
			if ($_POST['resumeid']){
				$return = $M->buyZdresume($_POST);
			}elseif ($_POST['wteid']){
				$return = $M->wtResume($_POST);
			}
			if($return['order']['order_id'] && $return['order']['id']){
				
				echo json_encode(array('error'=>0,'orderid'=>$return['order']['order_id'],'id'=>$return['order']['id']));
			}else{
				
				echo json_encode(array('error'=>1,'msg'=>$return['error']));
			}
		}else{
			echo json_encode(array('error'=>1,'msg'=>'参数错误，请重试！'));
		}
	}
	function resume_ajax_action(){
		$this->select_resume('resume_'.$_POST['type'],$_POST['id']);
	}
	function refresh_action(){
		$id=(int)$_GET['id'];
		$nid=$this->obj->update_once('resume_expect',array('lastupdate'=>time()),array('id'=>$id,'uid'=>$this->uid));
		$this->obj->update_once('resume',array('lastupdate'=>time()),array('uid'=>$this->uid));
		$nid?$this->layer_msg('刷新成功！',9,0):$this->layer_msg('刷新失败！',8,0);
 	}
	
	
	function resumerefresh_action(){
		
		$nid=$this->obj->DB_update_all("resume_expect","`lastupdate`='".time()."'","`uid`='".$this->uid."' and `id`='".(int)$_POST['id']."'");
		if($nid){
			$this->obj->DB_update_all("resume","`lastupdate`='".time()."'","`uid`='".$this->uid."'");							
			echo 1;
		}
		$id=(int)$_POST['id'];
		if(!empty($id)){
		  $this->obj->DB_update_all("resume_expect","jobstatus='".$_POST[jobstatus]."'","id='".$id."'");
		}
 	}

    function defaultresume_action(){
		$eid=(int)$_GET['id'];
		$row=$this->obj->DB_select_once("resume_expect","`id`='".$eid."' and `uid`='".$this->uid."'","`id`,`topdate`,`top`");
		if($row['id']){
			$val='';
			if($row['topdate']<time()){
				$val.=",`topdate`='',`top`=''";
			}
			$this->obj->DB_update_all("resume","`def_job`='".$row['id']."'","`uid`='".$this->uid."'");
			$this->obj->DB_update_all("resume_expect","`defaults`='0'".$val,"`uid`='".$this->uid."'");
			$this->obj->DB_update_all("resume_expect","`defaults`='1'","`uid`='".$this->uid."' and `id`='".$row['id']."'");
			$this->obj->member_log("设置默认简历");
		}
		$this->layer_msg('操作成功！',9,0,"index.php?c=resume");
	}
}
?>