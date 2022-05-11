<?php
/**
 * +----------------------------------------------------------------------
 * Jwt验证类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Jwt {
	/**
	 * header
	*/
    private $header = [
        'alg'=>'HS256',
        'typ'=>'JWT'
	];
	/**
	 * payload
	*/
	private $payload = [
		'iss' => '', // 签发者
		'sub' => '', // 所面向的用户
		'aud' => '', // 接收jwt的一方
		'exp' => '', // jwt的过期时间，这个过期时间必须要大于签发时间
		'nbf' => '', // 定义在什么时间之前，该jwt都是不可用的
		'iat' => '', // jwt的签发时间
		'jti' => '', // jwt的唯一身份标识，主要用来作为一次性token,从而回避重放攻击。
	];
	/**
	 * secret
	*/
	private $secret;

	/**
	 * 初始化参数
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.27
	*/
	public function __construct() {
		$config = \x\Config::get('jwt');
		$this->secret = $config['jwt_secret'];
		$this->payload['iss'] = $config['jwt_iss'];
		$this->payload['exp'] = (time()+$config['jwt_exp']);
		$this->payload['nbf'] = (time()+$config['jwt_nbf']);
	}

	/**
	 * 生成请求令牌
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.27
	 * @return string
	*/
    public function create_token($payload=[]) {
		$this->payload = array_merge($this->payload, $payload);
		// 为空默认生成唯一标识
		if (empty($this->payload['jti'])) {
			$this->payload['jti'] = md5(uniqid('JWT').time());
		}

		$base_header = $this->base64UrlEncode(json_encode($this->header,JSON_UNESCAPED_UNICODE));
		$base_payload = $this->base64UrlEncode(json_encode($this->payload,JSON_UNESCAPED_UNICODE));
		$token= $base_header.'.'.$base_payload.'.'.$this->signature($base_header.'.'.$base_payload, $this->secret, $this->header['alg']);

		return $token;
	}

	/**
	 * 验证token是否有效
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @param string $token
     * @return bool
	*/
    public function is_token($token) {
		$tokens = explode('.', $token);
        if (count($tokens) != 3) return false;

		list($base_header, $base_payload, $sign) = $tokens;

		//获取jwt算法
		$base_decode_header = json_decode($this->base64UrlDecode($base_header), JSON_OBJECT_AS_ARRAY);
		if (empty($base_decode_header['alg'])) return false;

		//签名验证
		if ($this->signature($base_header . '.' . $base_payload, $this->secret, $base_decode_header['alg']) !== $sign) return false;

		$payload = json_decode($this->base64UrlDecode($base_payload), JSON_OBJECT_AS_ARRAY);
		//签发时间大于当前服务器时间验证失败
		if (isset($payload['iat']) && $payload['iat'] > time()) return false;

		//过期时间小宇当前服务器时间验证失败
		if (isset($payload['exp']) && $payload['exp'] < time()) return false;

		//该nbf时间之前不接收处理该Token
		if (isset($payload['nbf']) && $payload['nbf'] > time()) return false;

        return $payload;
	}
	
	/**
	 * JWT.io 中base64UrlEncode编码实现
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @param string $input 需要编码的字符串
	 * @return string
	*/
	private function base64UrlEncode(string $input) {
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	/**
	 * JWT.io 中base64UrlEncode解码实现
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @param string $input 需要解码的字符串
	 * @return bool|string
	 */
	private function base64UrlDecode(string $input) {
		$remainder = strlen($input) % 4;
		if ($remainder) {
			$addlen = 4 - $remainder;
			$input .= str_repeat('=', $addlen);
		}
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
	 * jwt.io 中HMACSHA256签名实现
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg   算法方式
     * @return mixed
	*/
    private function signature(string $input, string $key, string $alg = 'HS256') {
        $alg_config=array(
            'HS256'=>'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key,true));
    }
}