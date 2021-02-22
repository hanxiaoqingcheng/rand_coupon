<?php

namespace Sy;

trait Coupon
{

    public $numericType = 1; //纯数字
    public $alphaType = 2; //纯字母
    public $alphaNumType = 3; //数字字母混合

    /**
     * 创建卡号和卡密
     *
     * @param int $num
     * @param string $prefix
     * @param int $codeLength
     * @param int $passwordLength
     * @param bool $ifNumeric
     * @param array $codeExcept
     * @return string|void
     */
    public function createCoupon(
        $num = 20,
        $prefix = '',
        $codeLength = 16,
        $passwordLength = 16,
        $ifNumeric = true,
        $codeExcept = []
    ) {
        set_time_limit(0);

        //处理缓冲区
        ob_end_clean();
        ob_implicit_flush(true);

        if ($num == 0) {
            return;
        }

        if ($ifNumeric) {
            $type = $this->numericType;
        } else {
            $type = $this->alphaNumType;
        }

        $couponCode = [];
        $prefixLen = strlen($prefix);
        $endLength = $codeLength - $prefixLen;

        if ($endLength >= 20) {
            $randLength = $endLength - 20;
            for ($i = 1; $i <= $num; $i++) {
                $endNo = date("YmdHis") . sprintf("%06d", $i);

                $code = $prefix . $endNo . $this->rand_str($randLength, $type);
                if ($codeExcept && in_array($code, $codeExcept)) {
                    $num = $num + 1;
                    continue;
                }
                $couponCode[] = [
                    'cardNo' => $code,
                    'password' => $this->createPassword()
                ];

            }
        } else {
            $substrLength = 20 - $endLength;

            for ($i = 1; $i <= $num; $i++) {
                $endNo = date("YmdHis") . sprintf("%06d", $i);
                $code = $prefix . substr($endNo, $substrLength);
                if ($codeExcept && in_array($code, $codeExcept)) {
                    $num = $num + 1;
                    continue;
                }
                $couponCode[] = [
                    'cardNo' => $code,
                    'password' => $this->createPassword($passwordLength)
                ];

            }

        }

        return json_encode($couponCode);

    }

    /**
     * 创建指定长度秘钥
     *
     * @param int $length
     * @param string $prefix
     * @return string
     */
    public function createPassword($length = 16, $prefix = '')
    {
        $randLength = $length - strlen($prefix);
        $passwordHead = $prefix . $this->rand_str($randLength - 1, $this->alphaNumType);
        $passSplit = str_split($passwordHead);
        $total = 0;
        foreach ($passSplit as $value) {
            if (is_numeric($value)) {
                $total = $total + $value;
            }
        }
        $remainder = $total % 9;
        if ($remainder == 0) {
            $password = $passwordHead . $this->rand_str(1, $this->alphaType);
        } else {
            $password = $passwordHead . (9 - $remainder);
        }
        return $password;

    }

    /**
     * 判断秘钥是否符合规范
     *
     * @param $password
     * @return bool
     */
    public function checkPassword($password)
    {
        $passSplit = str_split($password);
        $total = 0;
        foreach ($passSplit as $value) {
            if (is_numeric($value)) {
                $total = $total + $value;
            }
            if(!in_array($value,['A','B','C','D','E','F','G','H','J','K','M','N','P','Q','E','S','T','U','V','W','X','Y',1,2,3,4,5,6,7,8,9,0])){
                return false;
            }
        }
        $remainder = $total % 9;
        if($remainder != 0){
            return false;
        }
        return true;
    }

    /**
     * 随机获取指定长度值
     * @param int $randLength
     * @param bool $ifNumeric
     * @return string
     */
    public function rand_str($randLength = 6, $type = 1)
    {
        if ($randLength < 1) {
            $randStr = '';
        } else {
            if ($type == 3) {
                $chars = 'ABCDEFGHJKMNPQESTUVWXY1234567890';
            } else {
                if ($type == 1) {
                    $chars = '1234567890';
                } else {
                    $chars = 'ABCDEFGHJKMNPQESTUVWXY';
                }
            }
            $len = strlen($chars);
            $randStr = '';
            for ($i = 0; $i < $randLength; $i++) {
                $randStr .= $chars[mt_rand(0, $len - 1)];
            }
        }
        return $randStr;
    }



}