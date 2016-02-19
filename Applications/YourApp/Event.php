<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Store;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 *
 * GatewayWorker开发参见手册：
 * @link http://gatewayworker-doc.workerman.net/
 */
class Event
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     * @link http://gatewayworker-doc.workerman.net/gateway-worker-development/onconnect.html
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 @see http://gatewayworker-doc.workerman.net/gateway-worker-development/send-to-client.html
        Gateway::sendToClient($client_id, "Hello $client_id");
        // 向所有人发送 @see http://gatewayworker-doc.workerman.net/gateway-worker-development/send-to-all.html
        Gateway::sendToAll("$client_id login");
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param string $message 具体消息
    * @link http://gatewayworker-doc.workerman.net/gateway-worker-development/onmessage.html
    */
   public static function onMessage($client_id, $message)
   {
		
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
 		$class 		= 	$message_data['mod'];
		$method 	=	$message_data['act'];
		$args		=	$message_data['args'];
		if(!($class&&$method&&$args))
		{
			return ;
		}
		if(file_exists(__DIR__.'/Controller/'.$class.'.class.php'))
		{
			require_once __DIR__.'/Controller/'.$class.'.class.php';		
			$mod	=	new $class;
			echo call_user_func_array(array($mod,$method), array($args,$client_id));
		}else
		{
			return ;
		}

		
        // 向所有人发送 @see http://gatewayworker-doc.workerman.net/gateway-worker-development/send-to-all.html
        //Gateway::sendToAll("$client_id said $message");
		
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 @see http://gatewayworker-doc.workerman.net/gateway-worker-development/send-to-all.html
       GateWay::sendToAll("$client_id logout");
	   $store = Store::instance('user');
	   $store->select(1);
	   $key	= 'wm_userid_'.$client_id;
	   $store->del($key); 
	   
	   
   }
}
