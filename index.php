<?php
  include_once 'aliyun-php-sdk-core/Config.php';
  use RingCentral\Psr7\Response;
  use Dm\Request\V20151123 as Dm;
  function handler($req, $context): Response{
      $fstring = file_get_contents("aliyunConfig.json");
      $conf = json_decode($fstring, true);
      $iClientProfile = DefaultProfile::getProfile($conf['region'], $conf['accessKeyId'], $conf['accessKeySecret']);
      $client = new DefaultAcsClient($iClientProfile);
      $request = new Dm\SingleSendMailRequest();
      $email_config = $conf['emailConfig'];
      $request->setAccountName($email_config['AccountName']);
      $request->setFromAlias($email_config['FromAlias']);
      $request->setAddressType(1);
      $request->setTagName("capture");
      $request->setReplyToAddress("false");

      $queries = $req->getQueryParams();
      $request->setToAddress($queries['address']);
      $request->setSubject($queries['subject']);
      $request->setHtmlBody($queries['contents']);
      $response_code = 200;
      $response_header = array('ServeBy' => ['php', 'faas']);
      $response_body = 'email send!';

      try {
          $response = $client->getAcsResponse($request);
          print_r($response);
      }
      catch (ClientException  $e) {
          $response_body = 'Oops!Client failed to send email!';
          print_r($e->getErrorCode());
          print_r($e->getErrorMessage());
      }
      catch (ServerException  $e) {
          $response_body = 'Oops!Server failed to send email!';
          print_r($e->getErrorCode());
          print_r($e->getErrorMessage());
      }
      return new Response(
          $response_code,
          $response_header,
          $response_body
      );
  }
?>
