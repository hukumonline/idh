<?php
class Kutu_Controller_Plugin_Store_Mailer
{
	function sendBankInvoiceToUser($orderId)
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$siteOwner = "Hukumonline";
		$siteName = $config->mail->sender->support->name;
		$contactEmail = $config->mail->sender->support->email;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowOrder = $tblOrder->find($orderId)->current();
		$userId = $rowOrder->userId;
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->find($userId)->current();
		
		$userEmail = $rowUser->email;
		$userFullname = $rowUser->fullName;
		
		$datePurchased = Kutu_Lib_Formater::get_date($rowOrder->datePurchased);
		
		if ($rowOrder->paymentMethodNote == "membership")
			$duedate = date("d/m/y",strtotime($rowOrder->invoiceExpirationDate));
		else
			$duedate = "-";
			
		
		
		$message = 
"Kepada Yth, 
$userFullname

Berikut kami beritahukan bahwa Invoice untuk anda sudah dibuat pada tanggal $datePurchased.

Dengan metode pembayaran: $rowOrder->paymentMethod

Untuk pembayaran dengan Transfer Bank anda bisa memilih lima opsi berikut:

BCA BANK, cabang Menara Imperium
No. Rek: 221-3028-707
PT. Justika Siar Publika

BANK BNI, cabang Dukuh Bawah
No. Rek: 0073957339
PT. Justika Siar Publika

Invoice # $rowOrder->invoiceNumber
Jumlah tagihan: Rp. $rowOrder->orderTotal
Jatuh tempo: $duedate

Anda bisa login di Ruang Konsumen untuk melihat status invoice ini atau melakukan pembayaran secara online di ".KUTU_ROOT_URL."/store/viewinvoice/orderId/$orderId. Setelah melakukan pembayaran lewat transfer bank, mohon segera melakukan konfirmasi pembayaran anda lewat ".KUTU_ROOT_URL."/store_payment/confirm

Salam,

Hukumonline
==============================";	

		$this->send($config->mail->sender->support->email, $config->mail->sender->support->name, 
				$userEmail, '', "Hukumonline Invoice: ". $rowOrder->invoiceNumber, $message);
	
	}
	function sendInvoiceToUser($orderId)
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$siteOwner = "Hukumonline";
		$siteName = $config->mail->sender->support->name;
		$contactEmail = $config->mail->sender->support->email;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowOrder = $tblOrder->find($orderId)->current();
		$userId = $rowOrder->userId;
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->find($userId)->current();
		
		$userEmail = $rowUser->email;
		$userFullname = $rowUser->fullName;
		
		$datePurchased = Kutu_Lib_Formater::get_date($rowOrder->datePurchased);
		
		if ($rowOrder->paymentMethodNote == "membership")
			$duedate = date("d/m/y",strtotime($rowOrder->invoiceExpirationDate));
		else
			$duedate = "-";
			
		
		$message = 
"Kepada Yth, 
$userFullname

Berikut kami beritahukan bahwa Invoice untuk anda sudah dibuat pada tanggal $datePurchased.

Dengan metode pembayaran: $rowOrder->paymentMethod

Invoice # $rowOrder->invoiceNumber
Jumlah tagihan: Rp. $rowOrder->orderTotal
Jatuh tempo: $duedate

Anda bisa login di Ruang Konsumen untuk melihat status invoice ini atau melakukan pembayaran secara online di ".KUTU_ROOT_URL."/store/viewinvoice/orderId/$orderId.

Terima kasih,

Hukumonline

==============================";	

		$this->send($config->mail->sender->support->email, $config->mail->sender->support->name, 
				$userEmail, '', "Hukumonline Invoice: ". $rowOrder->invoiceNumber, $message);
	
	}
	public function sendReceiptToUser($orderId, $paymentMethod='', $statusText='')
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'general');
		
		$siteOwner = "Hukumonline";
		$siteName = $config->mail->sender->support->name;
		$contactEmail = $config->mail->sender->support->email;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowOrder = $tblOrder->find($orderId)->current();
		$userId = $rowOrder->userId;
		
		//first check if orderId status is PAID, then send the email.
		
		switch ($rowOrder->orderStatus)
		{
			case 1:
				die('ORDER STATUS IS NOT YET PAID. CAN NOT SEND RECEIPT!.');
				break;
			case 3:
				$orderStatus = "PAID";
				break;
			case 5:
				$orderStatus = "POSTPAID PENDING";
				break;
			case 6:
				$orderStatus = "PAYMENT REJECTED";
				break;
			case 7:
				$orderStatus = "PAYMENT ERROR";
				break;
			default:
				$orderStatus = "PAYMENT ERROR";
				break;
		}
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->find($userId)->current();
		
		$userEmail = $rowUser->email;
		$userFullname = $rowUser->fullName;
		
		switch(strtolower($paymentMethod))
		{
			case 'paypal':
			case 'manual':
			case 'bank':
			case 'postpaid':
			default:
				$message = 
"					
Dear $userFullname,

This is a payment receipt for Invoice # $rowOrder->invoiceNumber

Total Amount: USD $rowOrder->orderTotal
Transaction #:
Total Paid: USD $rowOrder->orderTotal
Status: $orderStatus
Your payment method is: $paymentMethod

You may review your invoice history at any time by logging in to your account ".KUTU_ROOT_URL."/store/payment/list

Note: This email will serve as an official receipt for this payment.

Salam,

Hukumonline

==============================";

		}
		
		$this->send($config->mail->sender->support->email, $config->mail->sender->support->name, 
				$userEmail, '', "Hukumonline Receipt Invoice# ". $rowOrder->invoiceNumber, $message);
	}
	
	public function sendUserBankConfirmationToAdmin($orderId)
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$sOrderId = '';
		
		if(is_array($orderId))
		{
			
			for($i=0; $i< count($orderId);$i++)
			{
				$sOrderId .= $orderId[$i].', ';
			}
		}
		else
		{
			$sOrderId = $orderId;
		}
		
		$message = 
"					
You just have received Bank Transfer Confirmation for Order ID $sOrderId please check thru admin page.".

KUTU_ROOT_URL."/admin/store/confirm.

==============================";


		$this->send($config->mail->sender->support->email, $config->mail->sender->support->name, 
						$config->mail->sender->billing->email, $config->mail->sender->billing->name, 
						"[HUKUMONLINE] Bank Transfer Payment Confirmation ", $message);
		
		
	}
	
	public function send($mailFrom, $fromName, $mailTo, $mailToName, $subject, $body)
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		$options = array('auth' => $config->mail->auth,
		                'username' => $config->mail->username,
		                'password' => $config->mail->password);
		
		if(!empty($config->mail->ssl))
		{
			$options = array('auth' => $config->mail->auth,
							'ssl' => $config->mail->ssl,
			                'username' => $config->mail->username,
			                'password' => $config->mail->password);
		}
			
		$transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);
		
		$mail = new Zend_Mail();
		$mail->setBodyText($body);
		$mail->setFrom($mailFrom, $fromName);
		$mail->addTo($mailTo, $mailToName);
		$mail->setSubject($subject);
		
		try 
		{
			$mail->send($transport);
		}
		catch (Zend_Exception $e)
		{
			echo $e->getMessage();
		}
	}
	
}