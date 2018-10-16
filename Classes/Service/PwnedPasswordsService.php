<?php
declare(strict_types = 1);
namespace Derhansen\FeChangePwd\Service;

/*
 * This file is part of the Extension "fe_change_pwd" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Class PwnedPasswordsService
 */
class PwnedPasswordsService
{
    const API_URL = 'https://api.pwnedpasswords.com/range/';

    /**
     * Checks the given password against data breaches using the haveibeenpwned.com API
     * Returns the amount of times the password is found in the haveibeenpwned.com database
     *
     * @param string $password
     * @return int
     */
    public function checkPassword(string $password)
    {
        $hash = sha1($password);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . substr($hash, 0, 5));
        curl_setopt($ch, CURLOPT_USERAGENT, 'TYPO3 Extension fe_change_pwd');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $results = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (($httpCode !== 200) || empty($results)) {
            // Something went wrong with the request, return 0 and ignore check
            return 0;
        }

        if (preg_match('/' . preg_quote(substr($hash, 5)) . ':([0-9]+)/ism', $results, $matches) === 1) {
            return $matches[1];
        }
        return 0;
    }
}
