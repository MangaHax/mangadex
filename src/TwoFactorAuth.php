<?php
/**
 * Created by PhpStorm.
 * User: Radon
 * Date: 20.12.2018
 * Time: 11:15
 */

namespace Mangadex;


use GAuth\Auth;

class TwoFactorAuth
{

    protected $user;

    protected $sql;

    protected $twofa;

    protected $_tmp_codes = null;

    protected $_tmp_usercode = null;

    const TYPE_ID = 1;

    public function __construct($user, $includeUnconfirmed = false)
    {
        global $sql;

        $this->user = $user;
        $this->sql = $sql;

        // Valid user
        if ($user !== null && $user->user_id > 1) {
            // Load from db
            $this->twofa = $this->sql->prep("user_2fa_".$user->user_id, "SELECT * FROM mangadex_user_2fa WHERE user_id = ? AND type >= ?", [$user->user_id, $includeUnconfirmed ? 0 : 1], 'fetch', \PDO::FETCH_ASSOC, -1);

            // Translate recovery codes
            if (is_array($this->twofa)) {
                $this->twofa['recovery'] = json_decode($this->twofa['recovery'], 1);
            } else {
                $this->twofa = null;
            }
        }
    }

    // Initalize 2FA for an user
    public function setUp()
    {
        // Already set up?
        if ($this->isEnabled())
            return false;

        // Initialize helper
        $gauth = new Auth();

        // Generate new recover codes & a secret
        $temp_recovery_codes = [
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
            $gauth->generateCode(8),
        ];
        $temp_secret = $gauth->generateCode();

        // Update db
        $this->sql->modify('user_2fa_setup', "DELETE FROM mangadex_user_2fa WHERE user_id = ? AND type = 0", [$this->user->user_id]);
        $this->sql->modify('user_2fa_setup', "INSERT INTO mangadex_user_2fa (user_id, secret, recovery) VALUES (?,?,?)", [
            $this->user->user_id,
            $temp_secret,
            json_encode($temp_recovery_codes)
        ]);

        // Update twofa
        $this->twofa = [
            'secret' =>     $temp_secret,
            'recovery' =>   $temp_recovery_codes,
            'type' =>       0
        ];

        return true;
    }

    // Disable 2FA for an user
    public function remove()
    {
        $this->sql->modify('user_2fa_remove', "UPDATE mangadex_user_2fa SET type = 0 WHERE user_id = ?", [$this->user->user_id]);
        $this->twofa['type'] = 0;
    }

    // Retrieve type of 2FA
    public function getType()
    {
        return $this->twofa['type'];
    }

    // Flags 2FA setup as complete in db
    public function confirmSetUp()
    {
        $this->sql->modify('user_2fa_setup', "UPDATE mangadex_user_2fa SET type = ? WHERE user_id = ?", [self::TYPE_ID, $this->user->user_id]);
        $this->twofa['type'] = self::TYPE_ID;
    }

    // Retrieve recover codes
    public function getRecoveryCodes()
    {
        return $this->twofa['recovery'];
    }

    // Check if code is correct
    public function validateLoginCode($code)
    {
        // No secret available?
        if (!isset($this->twofa['secret'])) {
            return false;
        }

        // Initialize new Auth with users secret & validate
        $gauth = new Auth($this->twofa['secret']);
        return $gauth->validateCode(str_replace(' ', '', $code), $this->twofa['secret']);
    }

    // Check if recovery code is correct
    public function validateRecoveryCode($code)
    {
        // No recovery codes available?
        if (!isset($this->twofa['recovery']) || empty($this->twofa['recovery']) || !is_array($this->twofa['recovery'])) {
            return false;
        }

        // Search for supplied key
        $key = array_search($code, $this->twofa['recovery']);
        if ($key !== false) {
            // Remove from recovery keys & update db
            unset($this->twofa['recovery'][$key]);
            $this->sql->modify(
                'user_2fa_setup',
                "UPDATE mangadex_user_2fa SET recovery = ? WHERE user_id = ?",
                [
                    json_encode(array_values($this->twofa['recovery'])),
                    $this->user->user_id
                ]
            );

            // Recovery successful
            return true;
        }

        // Key not found
        return false;
    }

    // Retrieve secret
    public function getUserCode()
    {
        return $this->twofa['secret'];
    }

    // Check if 2FA is even enabled
    public function isEnabled()
    {
        return $this->twofa !== null;
    }

    // Generate qr-code for easy setup
    public function generateQrImageData()
    {
        // Initialize new Auth with users secret & generate qr
        $gauth = new Auth($this->twofa['secret']);
        return base64_encode($gauth->generateQrImage($this->user->email, 'MangaDex.org', 200));
    }

    // Retrieve twofa
    public function getData()
    {
        return $this->twofa;
    }

}