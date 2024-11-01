<?php
/*
 * Copyright 2015 Cyclos (www.cyclos.org)
 *
 * This plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * Be aware the plugin is publish under GPLv2 Software, but Cyclos 4 PRO is not,
 * you need to buy an appropriate license if you want to use Cyclos 4 PRO, see:
 * www.cyclos.org.
 */

// Block people to access the script directly (against malicious attempts)
defined ( 'ABSPATH' ) or die ( 'No script kiddies please!' );
function cyclosUsersMap_decrypt_password($encryptedPassword) {
	$key = "/r`v&cZkW6LEozST'%p8rr>nAb}n.p";
	$c = base64_decode ( $encryptedPassword );
	$ivlen = openssl_cipher_iv_length ( $cipher = "AES-128-CBC" );
	$iv = substr ( $c, 0, $ivlen );
	$hmac = substr ( $c, $ivlen, $sha2len = 32 );
	$ciphertpassword_raw = substr ( $c, $ivlen + $sha2len = 32 );
	return openssl_decrypt ( $ciphertpassword_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv );
}

?>
