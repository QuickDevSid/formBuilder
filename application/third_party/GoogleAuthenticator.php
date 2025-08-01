<?php
/**
 * PHPGangsta_GoogleAuthenticator class
 * Compatible with Microsoft Authenticator / Google Authenticator (TOTP)
 */

class PHPGangsta_GoogleAuthenticator
{
    protected $_codeLength = 6;

    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();
        unset($validChars[32]);

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }

    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);

        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);

        $hm = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);

        $value = unpack("N", $hashpart)[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);

        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    public function getQRCodeGoogleUrl($name, $secret, $title = null, $params = [])
    {
        $width = isset($params['width']) ? $params['width'] : 200;
        $height = isset($params['height']) ? $params['height'] : 200;
        $level = isset($params['level']) ? $params['level'] : 'M';

        $otpauth = 'otpauth://totp/'.urlencode($name).'?secret='.$secret;
        if ($title) {
            $otpauth .= '&issuer='.urlencode($title);
        }

        return 'https://chart.googleapis.com/chart?chs=' . $width . 'x' . $height .
            '&chld=' . $level . '|0&cht=qr&chl=' . urlencode($otpauth);
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }

        return false;
    }

    protected function _base32Decode($secret)
    {
        if (empty($secret)) return '';

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) return false;

        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) return false;
        }

        $secret = str_replace('=', '', $secret);
        $secret = strtoupper($secret);
        $binaryString = '';

        for ($i = 0; $i < strlen($secret); $i += 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) return false;

            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);
            foreach ($eightBits as $char) {
                $binaryString .= chr(base_convert($char, 2, 10));
            }
        }

        return $binaryString;
    }

    protected function _getBase32LookupTable()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        ];
    }
}
