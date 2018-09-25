<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class emailconfiglist_controller extends adminCommon{
	function index_action(){
		
		$where="1";
		if($_GET['state']=="1"){
			$where.=" and `state`='1'";
			$urlarr['state']='1';
		}elseif($_GET['state']=="2"){
			$where.=" and `state`<>'1'";
			$urlarr['state']='2';
		}
		

		if(trim($_GET['keyword'])){
			$_GET['keyword'] = trim($_GET['keyword']);

			if ($_GET['type']=='1'){
				$where.=" and `email` like '%".$_GET['keyword']."%'";
			}else if($_GET['type']=='3'){
				$where.=" and `name` like '%".$_GET['keyword']."%'";
			}else if($_GET['type']=='2'){
				 $where.=" and `cname` like '%".$_GET['keyword']."%'";
			}else if($_GET['type']=='4'){
				 $where.=" and `smtpserver` like '%".$_GET['keyword']."%'";
			}
			$urlarr['type']=$_GET['type'];
			$urlarr['keyword']=$_GET['keyword'];
		}
		if(($_GET['date'])&&$_GET['time']<1){
			$times=@explode('~',$_GET['date']);
			$where.=" and `ctime` >= '".strtotime($times[0]." 00:00:00")."' and `ctime`<'".strtotime($times[1]." 23:59:59")."'";
			$urlarr['date']=$_GET['date'];
		}
		if($_GET['time']){
			if($_GET['time']=='1'){
				$where.=" and `ctime` >= '".strtotime(date("Y-m-d 00:00:00"))."'";
			}else{
				$where.=" and `ctime` >= '".strtotime('-'.$_GET['time'].'day')."'";
			}
			unset($_GET['sdate']);
			unset($_GET['edate']); 
			$urlarr['time']=$_GET['time'];
		}
		if($_GET['order']){
			if($_GET['order']=="desc"){
				$order=" order by `".$_GET['t']."` desc";
			}else{
				$order=" order by `".$_GET['t']."` asc";
			}
			$urlarr['t']=$_GET['t'];
			$urlarr['order']=$_GET['order'];
		}else{
			$order=" order by `id` desc";
		}
		if($_GET['order']=="asc"){
			$this->yunset("order","desc");
		}else{
			$this->yunset("order","asc");
		}
		$urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		$rows=$this->get_page("email_msg",$where.$order,$pageurl,$this->config['sy_listnum']);
		$search_list[]=array("param"=>"state","name"=>'发送状态',"value"=>array("1"=>"发送成功","2"=>"发送失败"));
		$lo_time=array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'); 
		$search_list[]=array("param"=>"time","name"=>'发送时间',"value"=>$lo_time);
		$this->yunset("search_list",$search_list);
		$this->yunset("get_type", $_GET);
		$this->yuntpl(array('admin/admin_emailmsg'));
	}
	function del_action(){ 
		if(is_array($_POST['del'])){
			$delid=@implode(',',$_POST['del']);
			$layer_type=1;
		}else{
			$this->check_token();
			$delid=(int)$_GET['id'];
			$layer_type=0;
		}
		if(!$delid){
			$this->layer_msg('请选择要删除的内容！',8,$layer_type,$_SERVER['HTTP_REFERER']);
		}
		$del=$this->obj->DB_delete_all("email_msg","`id` in ($delid)"," ");
		$del?$this->layer_msg('邮件记录(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
	}
}

?>