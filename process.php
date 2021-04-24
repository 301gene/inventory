<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/usr/share/php/libphp-phpmailer/src/Exception.php';
require '/usr/share/php/libphp-phpmailer/src/PHPMailer.php';
require '/usr/share/php/libphp-phpmailer/src/SMTP.php';

// Include the main TCPDF library (search for installation path).
require_once('/usr/share/php/tcpdf/autoload.php');


$recipient_email    = "eugene@solidaritymovers.com"; //recepient
$from_email         = "Inventory Update Form <form@localhost>"; // default from email
$subject            = "An inventory just updated inventory on site!"; // default email subject

    if (isset($_POST["signature"]))
    {

        // save images' base64 encoded strings
        $img_base64_encoded = $_POST["signature"];
        $img2_base64_encoded = $_POST["signature2"];

        // add a '@' before the base64 encoded string
        $image = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '">';
        $image2 = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img2_base64_encoded) . '">';
        $inventory = filter_var($_POST["result"], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        $addItems = filter_var($_POST["additionalItems"], FILTER_SANITIZE_STRING);
        $date1 = $_POST["date1"];
        $date2 = $_POST["date2"];

        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {

            //Page header
            public function Header() {
                // Logo
                $image_file = 'logo.jpg';
                $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '<< INVENTORY SHEET >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }

            // Page footer
            public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);
                // Page number
                $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }


        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Solidarity Movers');
        $pdf->SetTitle('Inventory Sheet');
        $pdf->SetSubject('Inventory Sheet');
        $pdf->SetKeywords('Inventory, list');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 12, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        // Set some content to print
        $html = nl2br($inventory) . "<br>";
        $html .= "ADDITIONAL ITEMS:<br><br>" . nl2br($addItems) . "<br>";

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        $pdf->AddPage();

        $html = "SHIPPER OR HIS/HER REPRESENTATIVE: <br>" . $image . "<hr><br>" . $date1 . "<br><br>";
        $html .= "MOVER REPRESENTATIVE: <br>" . $image2 . "<hr><br>" . $date2 . "<br>";

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        /*$pdf->Output('example_001.pdf', 'I');*/

        $attachment = $pdf->Output('example_001.pdf', 'S');

        //============================================================+
        // END OF FILE
        //============================================================+


        $mail = new PHPMailer;
        $mail->setFrom('solidaritymovers@gmail.com');
        $mail->addAddress($recipient_email);
        $mail->AddStringAttachment($attachment, 'example_001.pdf', 'base64', 'application/pdf');
        $mail->Subject = 'Message sent by PHPMailer';
        $mail->Body = 'Hello! use PHPMailer to send email using PHP';
        $mail->IsSMTP();
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'ssl://smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Port = 465;

        //Set your existing gmail address as user name
        $mail->Username = 'solidaritymovers@gmail.com';

        //Set the password of your gmail address here
        $mail->Password = '$olidfr66832';
        if(!$mail->send()) {
          echo 'Email is not sent.';
          echo 'Email error: ' . $mail->ErrorInfo;
        } else {
          echo '<div style="color: green;">Email has been sent.</div>';
          echo nl2br($inventory) . "<br>";
          echo "ADDITIONAL ITEMS:<br><br>" . nl2br($addItems) . "<br>";
          echo "<img src=\"" . $img_base64_encoded . "\"><br>";
          echo $date1 . "<br>";
          echo "<img src=\"" . $img2_base64_encoded . "\"><br>";
          echo $date2 . "<br>";
        }

    }
?> 