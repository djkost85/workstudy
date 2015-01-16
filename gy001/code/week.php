<?php
/**
 * 服务器端更新类，用于远程采集和远程下载
 * @author Askuy <yoyoloves@qq.com>
 */

class UseHttp  {

  //curl 对象
  private $ch;
  // 在HTTP请求中包含一个”user-agent”头的字符串
  private static $userAgent = 'askuy'; 
  private static $paramsOnUrlMethod = array('GET','DELETE');

  // 主机地址
  //public $hostUrl = 'http://localhost/lycms/';
  public $hostUrl = 'http://ly.airshe.com/';
  // 在发起连接前等待的时间，如果设置为0，则不等待
  public $connectTimeout = 30; 
  // 设置curl允许执行的最长秒数
  public $timeout = 0;
  // 验证对方提供的（读取https）证书是否有效，过期，或是否通过CA颁发的
  public $sslVerifyPeer = false; 
  
  // 最后一次请求的url地址
  private $httpUrl;
  // 最后一次HTTP信息
  public $httpInfo = array();
  // 最后一次头信息
  public $httpHeader = array();
  // 最后一次HTTP状态信息
  public $httpCode;
  // 最后一次post数据
  private $postFields;
  private $contentType;
  #For tmpFile
  private $file = null;
  // 远程文件下载到临时文件夹里
  private $tmpFile = null;
  // 是否debug
  public $debug = false;
  public $cookie = '';
  public $cookiePath  = '';
  public $cookieSave = 0;

  public function __construct(){

    $this->ch = curl_init();
    /* curl settings */
    curl_setopt($this->ch, CURLOPT_USERAGENT, self::$userAgent); // 在HTTP请求中包含一个”user-agent”头的字符串。
    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout); // 在发起连接前等待的时间，如果设置为0，则不等待
    curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout); // 设置curl允许执行的最长秒数
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE); // 讲curl_exec()获取的信息以文件流的形式返回，而不是直接输出
    curl_setopt($this->ch, CURLOPT_AUTOREFERER, TRUE); // 自动设置header中的referer信息
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE); //  设置这个选项为一个非零值(象 “Location: “)的头，服务器会把它当做HTTP头的一部分发送(注意这是递归的，PHP将发送形如 “Location: “的头);启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:')); // 设置一个header中传输内容的数组
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer); //验证对方提供的（读取https）证书是否有效，过期，或是否通过CA颁发的
    curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'getHeader')); // 设置一个回调函数，这个函数有两个参数，第一个是curl的资源句柄，第二个是输出的header数据。header数据的输出必须依赖这个函数，返回已写入的数据大小
    curl_setopt($this->ch, CURLOPT_HEADER, FALSE); // 设定是否输出页面内容
  }

  /**
   * 总的方法调用curl
   * @param string $url
   * @param string $method 'POST','GET','DELETE','PUT'
   * @param array $postFields array('name'=>3,'test'=>4),todo：支持字符串写法
   * @param string $username 页面权限用户名
   * @param string $password 页面权限密码
   * @param string $contentType 要求返回类型Content-Type:text/html
   * @return mixed $response 返回信息，一般为json信息和xml信息，看有没有必要对其解析。
   */
  public function call($url,$method,$postFields=null,$username=null,$password=null,$contentType=null){

    if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0 ) {
      $url = "{$this->hostUrl}{$url}";
    }

    $this->httpUrl      = $url;
    $this->contentType  = $contentType;
    $this->postFields   = $postFields;

    $url                = in_array($method, self::$paramsOnUrlMethod) ? $this->getRequestUrl() : $this->getHttpUrl();
    is_object($this->ch) or $this->__construct();

    switch ($method) {
      case 'POST':
      curl_setopt($this->ch, CURLOPT_POST, TRUE);
      if ($this->postFields != null) {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
      }
      break;
      case 'DELETE':
      curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      break;
      case 'PUT':
      curl_setopt($this->ch, CURLOPT_PUT, TRUE);
      if ($this->postFields != null) {
        $this->file = tmpFile();
        fwrite($this->file, $this->postFields);
        fseek($this->file, 0);
        curl_setopt($this->ch, CURLOPT_INFILE,$this->file);
        curl_setopt($this->ch, CURLOPT_INFILESIZE,strlen($this->postFields));
      }
      break;
    }

    $this->setAuthorizeInfo($username, $password);
    $this->contentType != null && curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-type:'.$this->contentType));

    if($this->cookie) {
      curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
    }
    if($this->cookiePath&&$this->cookieSave) {
      $this->cleanCookie();
      curl_setopt($this->ch, CURLOPT_COOKIEJAR,  $this->cookiePath); //存储cookies
    }
    if($this->cookiePath&&!$this->cookieSave) {
      curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookiePath); //使用上面获取的cookies
    }
    curl_setopt($this->ch, CURLOPT_URL, $url);

    $response = curl_exec($this->ch);
    $this->httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    $this->httpInfo = array_merge($this->httpInfo, curl_getinfo($this->ch));
    
    if ($this->debug) $this->showDebug($response);
    $this->close();
    return $response;
  }

  /**
  *****************************这个是对call的学习，上线后删掉这个*****************************
   * 远程post数据，格式$http->http('http://localhost/lycms/Admin/Test/curlPost.html','POST','name=3&test=4');
   * 远程post数据，格式$http->http('Admin/Test/curlPost.html','POST','name=3&test=4');
  *****************************这个是对call的学习，上线后删掉这个*****************************
   */
  public function http($url, $method, $postFields = NULL, $header = array()) {
    if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0 ) {
      $url = "{$this->hostUrl}{$url}";
    }

    $this->httpUrl     = $url;
    $this->httpHeader  = $header;
    $this->postFields  = $postFields;

    switch ($method) {
      case 'POST':
        curl_setopt($this->ch, CURLOPT_POST, TRUE);
        if (!empty($this->postFields)) {
          curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
        }
        break;
      case 'DELETE':
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if (!empty($this->postFields)) {
          $url = "{$url}?{$this->postFields}";
        }
    }
    if($this->cookie) {
      curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
    }

    curl_setopt($this->ch, CURLOPT_URL, $this->httpUrl);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);
    // curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, array($this, 'setWrite')); 
    curl_setopt($this->ch, CURLINFO_HEADER_OUT, TRUE);

    $response = curl_exec($this->ch);

    $this->httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    $this->httpInfo = array_merge($this->httpInfo, curl_getinfo($this->ch));

    if ($this->debug) $this->showDebug($response);
    curl_close ($this->ch);
    return $response;
  }

  public function curlDown($remote, $local) {
    set_time_limit(0);
    $fp = fopen($local,"w");
    $this->tmpFile = $local;
    curl_setopt($this->ch, CURLOPT_URL, $remote);
    curl_setopt($this->ch, CURLOPT_FILE, $fp);
    curl_setopt($this->ch, CURLOPT_HEADER, 0);
    curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, array($this,'setWrite'));
    curl_exec($this->ch);
    curl_close($this->ch);
    fclose($fp);
  }


  /**
   * 解析地址，并且重构
   * scheme://host/path
   */
  public function getHttpUrl() {
    $parts = parse_url($this->httpUrl);

    $port   = @$parts['port'];
    $scheme = $parts['scheme'];
    $host   = $parts['host'];
    $path   = @$parts['path'];

    $port or $port = ($scheme == 'https') ? '443' : '80';

    if (($scheme == 'https' && $port != '443')
      || ($scheme == 'http' && $port != '80')) {
      $host = "$host:$port";
    }
    return "$scheme://$host$path";
  }

  /**
   * 建立一个get请求的url地址
   */
  public function getRequestUrl() {
    $postData = http_build_query($this->postFields);

    $out = $this->getHttpUrl();
    if ($postData) {
      $out .= '?'.$postData;
    }
    return $out;
  }

  /**
  * 有页面访问控制的页面
  * 详情http://blog.51yip.com/php/1039.html
  * http://blog.51yip.com/apachenginx/1051.html
  * @param $username String
  * @param $password String
  * @return void
  */
  public function setAuthorizeInfo($username,$password) {
    if($username != null) { #The password might be blank
      curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($this->ch, CURLOPT_USERPWD, "{$username}:{$password}");
    }
  }

  public function getHeader($ch, $header) {
    $i = strpos($header, ':');
    if (!empty($i)) {
      $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
      $value = trim(substr($header, $i + 2));
      $this->httpHeader[$key] = $value;
    }
    return strlen($header);
  }

  public function setWrite($ch, $content) {
    ob_start();
    file_put_contents($this->tmpFile, $content, FILE_APPEND);
    echo round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD)/curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD)*100,2)."<br/>";
    echo ob_get_clean();
    ob_flush();
    flush();
    usleep(100000);
    return strlen($content);
  }

  public function close() {
    curl_close($this->ch);
    if($this->file !=null) {
      fclose($this->file);
    }
  }

  public function showDebug($response) {
    echo "<pre>";
    echo "=====post data======\r\n";
    print_r($this->postFields);

    echo "=====headers======\r\n";
    print_r($this->httpHeader);

    echo '=====request info====='."\r\n";
    print_r($this->httpInfo);

    echo '=====response====='."\r\n";
    print_r($response);
  }

  // 判断每次缓存文件是否存在，存在删除
  private function cleanCookie () {
    if(!$this->cookiePath) return;

    // 如果存在，说明上次已经保存过该文件，清除上次缓存
    if(file_exists($this->cookiePath)) {
      @unlink($this->cookiePath);
    }

    // 重新生成缓存文件
    if(!file_exists($this->cookiePath)) {
      $this->makeDir(dirname($this->cookiePath));
      $this->makeFile($this->cookiePath);
    }
  }

  // 循环创建目录
  private function makeDir($dir, $mode = 0755) {
    if (is_dir($dir) || @mkdir($dir,$mode)) return true;
    if (!$this->makeDir(dirname($dir),$mode)) return false;
      return @mkdir($dir,$mode);
  }

  // 创建文件
  private function makeFile($path) {
    return fopen($path, "w");
  }


}

    $http = new UseHttp();
    // curl必须使用绝对路径,DOC_ROOT是我在index.php定义的常量，显示未E://path
    $http->cookiePath = '/alidata/www/phpwind/gy/cookie.txt';
    // 设为1后，存储cookie前，内部会自动清除上次cookie
    $http->cookieSave = 1;
    $http->call('http://xueqiu.com/P/ZH016293','GET');
    // 设为0后，不存储cookie，内部会使用上次cookie
    $http->cookieSave = 0;
	$cube_str='ZH002754,ZH001160,ZH003926,ZH025370,ZH001259,ZH003728,ZH015588,ZH008950,ZH009732,ZH095301,ZH011113,ZH000655,ZH004610,ZH009092,ZH003331,ZH028233,ZH016293,ZH003689,ZH022991,ZH001753,ZH002422,ZH010687,ZH003694,ZH004266,ZH000724,ZH026320,ZH087179,ZH021833,ZH020780,ZH002347,ZH008038,ZH086271,ZH002120,ZH015808,ZH001301,ZH005627,ZH000283,ZH018203,ZH006080,ZH004124,ZH090854,ZH021628,ZH006699,ZH002153,ZH008669,ZH008875,ZH000661,ZH004581,ZH001456,ZH005631,ZH004470,ZH004379,ZH001393,ZH003700,ZH006105,ZH067380,ZH000889,ZH019124,ZH052204,ZH091546,ZH001823,ZH016751,ZH004988,ZH004598,ZH001396,ZH006916,ZH004858,ZH003926,ZH025370,ZH003728,ZH002233,ZH004938,ZH000464,ZH008957,ZH001463';
    $response = $http->call('http://xueqiu.com/cubes/quote.json','GET',array('code'=>$cube_str));
	$arr = json_decode($response,TRUE);
	$week = date('W',time());//第几周
	foreach($arr as $k=>$v){
		file_put_contents('/alidata/www/phpwind/gy/weekdata/'.$k.'_daily_gain_'.$week.'.txt',$v['daily_gain'].'|',FILE_APPEND);
	}