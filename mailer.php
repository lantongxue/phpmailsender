<?php

/**
 * 邮件发送者
 * 暂不支持添加附件
 */
class Mailer
{
	private $_socket = null;

	protected $smtp_host = "";

	protected $smtp_port = 25;

	protected $transports = "tcp";

	protected $host_name = "";

	protected $user_name = "";

	protected $password = "";

	protected $isHtml = false;

	protected $charset = "UTF-8";

	protected $from = "";

	protected $to = array();

	protected $cc = array();

	protected $bcc = array();

	protected $subject = "";

	protected $body = "";

	protected $debug = false;
	
	/**
	 * 基本参数配置
	 * smtp_host、smtp_port、transports、host_name、user_name、password、isHtml、charset、debug
	 * 构造函数仅初始化以上几个核心参数，其他参数可以通过调用setOption方法配置
	 * @param [array] $_options
	 */
	public function __construct($_options)
	{
		if( empty($_options) )
		{
			throw new \Exception("options can not be empty !");
		}

		$this->smtp_host = $_options['smtp_host'];

		$this->smtp_port = isset($_options['smtp_port']) ? $_options['smtp_port'] : 25;

		$this->transports = isset($_options['transports']) ? $_options['transports'] : "tcp";

		$this->host_name = isset($_options['host_name']) ? $_options['host_name'] : $this->getHostName();

		$this->user_name = $_options['user_name'];

		$this->password = $_options['password'];

		$this->isHtml = isset($_options['isHtml']) ? $_options['isHtml'] : false;

		$this->charset = isset($_options['charset']) ? $_options['charset'] : 'UTF-8';

		$this->debug = isset($_options['debug']) ? $_options['debug'] : false;
	}
	
	/**
	 * 设置参数
	 *
	 * @param [mixed] $param 参数名称
	 * @param [mixed] $value 值
	 * @return object
	 */
	public function setOption($param, $value)
	{
		$this->$param = $value;

		return $this;
	}

	/**
	 * 启用支持HTML格式
	 *
	 * @param boolean $enable 是否启用
	 * @return object
	 */
	public function enableHtml($enable = false)
	{
		$this->isHtml = $enable;

		return $this;
	}

	/**
	 * 启用支持SSL协议
	 *
	 * @param boolean $enable 是否启用
	 * @return object
	 */
	public function enableSSL($enable = false)
	{
		if( $enable )
		{
			$this->transports = "ssl";
		}

		return $this;
	}

	/**
	 * 启用支持TLS协议
	 *
	 * @param boolean $enable 是否启用
	 * @return object
	 */
	public function enableTLS($enable = false)
	{
		if( $enable )
		{
			$this->transports = "tls";
		}

		return $this;
	}

	/**
	 * 设置邮件主题
	 *
	 * @param [string] $subject 主题
	 * @return object
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;

		return $this;
	}
	
	/**
	 * 设置邮件内容
	 *
	 * @param [string] $body 邮件内容。可以是html代码
	 * @return object
	 */
	public function setBody($body)
	{
		$this->body = $body;

		return $this;
	}
   
   	/**
	 * 发送者
	 *
	 * @param [string] $from 发送者
	 * @return object
	 */
	public function setFrom($from)
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * 接收者
	 *
	 * @param [mixed] $to 接收者
	 * @return object
	 */
	public function setTo($to)
	{
		if( is_array($to) && isset($to[0]) ) // 如果设置的是完整数组，则直接赋值
		{
			$this->to = $to;
		}
		else 
		{
			$this->to[] = $to;
		}

		return $this;
	}

	/**
	 * 设置抄送人员地址
	 *
	 * @param [mixed] $cc 抄送人员地址
	 * @return object
	 */
	public function setCC($cc)
	{
		if( is_array($cc) && isset($cc[0]) ) // 如果设置的是完整数组，则直接赋值
		{
			$this->cc = $cc;
		}
		else 
		{
			$this->cc[] = $cc;
		}

		return $this;
	}

	/**
	 * 设置秘密抄送者地址
	 *
	 * @param [mixed] $bcc 秘密抄送者地址
	 * @return object
	 */
	public function setBCC($bcc)
	{
		if( is_array($bcc) && isset($bcc[0]) ) // 如果设置的是完整数组，则直接赋值
		{
			$this->bcc = $bcc;
		}
		else 
		{
			$this->bcc[] = $bcc;
		}

		return $this;
	}

	/**
	 * 获取当前主机名
	 *
	 * @return string
	 */
	public function getHostName()
	{
		return gethostname();
	}

	/**
	 * 生成邮件协议头
	 *
	 * @return string
	 */
	protected function buildData()
	{
		$data = "";

		$data .= "MIME-Version:1.0\r\n";
		
		if( is_array($this->from) )
		{
			$data .= sprintf('From:%1$s <%2$s>', $this->from['label'], $this->from['address'])."\r\n";
		}
		else
		{
			$data .= sprintf('From:<%1$s>', $this->from)."\r\n";
		}

		// 设置收件人
		$data .= $this->eachTo($this->to, "To");

		// 设置抄送
		$data .= $this->eachTo($this->cc, "CC");

		// 设置秘密抄送
		$data .= $this->eachTo($this->bcc, "BCC");        
		
		$data .= sprintf('Subject:%1$s', $this->subject)."\r\n";

		if( $this->isHtml )
		{
			$data .= sprintf('Content-Type:text/html;charset=%1$s', $this->charset)."\r\n\r\n";
		}
		else
		{
			$data .= sprintf('Content-Type:text/plain;charset=%1$s', $this->charset)."\r\n\r\n";
		
		}

		$data .= $this->body."\r\n";  

		$data .= "\r\n.\r\n";

		return $data;
	}

	/**
	 * 解析接收者、抄送者、秘密抄送者数据
	 *
	 * @param [array] $list 收者、抄送者、秘密抄送者数据
	 * @param [string] $header To、CC、BCC
	 * @return string
	 */
	protected function eachTo($list, $header)
	{
		if( is_array($list) && !empty($list) )
		{
			$_address = "";
			
			foreach($list as $_item)
			{
				if( is_string($_item) )
				{
					$_address .= sprintf('<%1$s>,', $_item);
				}

				if( is_array($_item))
				{
					$_address .= sprintf('%1$s <%2$s>,', $_item['label'], $_item['address']);
				}
			}

			$_address = rtrim($_address, ",");

			return sprintf($header.':%1$s', $_address)."\r\n";
		}
		else if( !empty($list) )
		{
			return sprintf($header.':<%1$s>', $this->bcc)."\r\n";
		}       
	}

	/**
	 * 解析接收者数据
	 *
	 * @param [array] &$command 存放命令的数组.指针
	 * @param [array] $list 接收者数据
	 * @return void
	 */
	protected function eachRCPT(&$command, $list)
	{
		if( is_array($list) && !empty($list) )
		{
			foreach($list as $_to)
			{
				if( is_string($_to) )
				{
					$command[] = array("RCPT TO:<" . $_to . ">\r\n", 250);
				}

				if( is_array($_to))
				{
					$command[] = array("RCPT TO:<" . $_to['address'] . ">\r\n", 250);
				}
			}
		}
	}

	/**
	 * 生成与SMTP交互的协议行
	 *
	 * @return array
	 */
	protected function buildCommand()
	{
		$command = array(
			array("HELO ".$this->host_name."\r\n", 250),
			array("AUTH LOGIN\r\n", 334),
			array(base64_encode($this->user_name) . "\r\n", 334),
			array(base64_encode($this->password) . "\r\n", 235),
		);


		if( is_array($this->from) )
		{
			$command[] = array("MAIL FROM:<" . $this->from['address'] . ">\r\n", 250);
		}
		else
		{
			$command[] = array("MAIL FROM:<" . $this->from . ">\r\n", 250);
		}

		$this->eachRCPT($command, $this->to);

		$this->eachRCPT($command, $this->cc);

		$this->eachRCPT($command, $this->bcc);
		
		$command[] = array("DATA\r\n", 354);

		$command[] = array($this->buildData(), 250);

		$command[] = array("QUIT\r\n", 221);

		return $command;
	}

	/**
	 * 创建一个支持ssl或tls的socket连接符
	 *
	 * @param string $protocol 协议【ssl,tls】
	 * @return resource
	 */
	protected function create_ssl_or_tls_socket($protocol = 'ssl')
	{
		$contextOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false
			)
		);

		$context = stream_context_create($contextOptions);

		$remote = $protocol."://".$this->smtp_host.":".$this->smtp_port;

		$socket = stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);

		return $socket;
	}

	/**
	 * 打开一个socket连接
	 *
	 * @return void
	 */
	protected function open()
	{
		switch($this->transports)
		{
			case 'tcp' :

				$this->_socket = fsockopen("tcp://".$this->smtp_host, $this->smtp_port);

				break;

			case 'ssl' : 

				$this->_socket = $this->create_ssl_or_tls_socket($this->transports);
			
				break;

			case 'tls' : 
			  
				$this->_socket = $this->create_ssl_or_tls_socket($this->transports);

				break;
		}

		$welcome = fgets($this->_socket);

		$this->debug("welcome:".$welcome."\n");
	}

	/**
	 * 关闭socket连接，并释放资源
	 *
	 * @return void
	 */
	protected function close()
	{
		fclose($this->_socket);
	}

	/**
	 * 发送邮件
	 *
	 * @return boolean 发送成功返回true。发送失败会直接抛出异常
	 */
	public function send()
	{
		$this->open();

		foreach($this->buildCommand() as $item)
		{
			fwrite($this->_socket, $item[0]);

			$result = fgets($this->_socket);

			if( strripos($result,strval($item[1])) === false)
			{
				throw new \Exception($result);
			}
			$message = "cmd:$item[0]"."result:$result"."\r\n";

			$this->debug($message);
		}
		
		$this->close();

		return true;
	}

	/**
	 * 输出调试信息
	 *
	 * @param [string] $message 输出的信息
	 * @return void
	 */
	protected function debug($message)
	{
		if( $this->debug === true )
		{
			echo $message;
		}
	}
}

///////////
//  demo
//////////

$options = array(
	'smtp_host' => 'smtp server',
	'smtp_port' => 25, // port support ssl,tls
	'user_name' => 'account@test.com',
	'password'  => 'password',
	'isHtml'    => true,
);

$m = new Mailer($options);

$m->setOption('debug', true);
//$m->enableSSL(true);
//$m->enableTLS(true);
// $to = array(
//     'account1@test.com',
//     'account2@test.com',
//     array('label'=>'test3','address'=>'account3@test.com'),
// );
// $m->setTo($to);

$m->setTo('account4@test.com');

//$m->setBCC('account5@test.com');
//$m->setCC('account6@test.com');

$m->setFrom(array('label'=>'管理员','address'=>'admin@test.com'));

$m->setSubject('验证码'.rand());

$m->setBody(rand().'test  body<h1>html</h1>');

$m->send();
