<?php

use PHPUnit\Framework\TestCase;

class DeliveryEmailTest extends TestCase
{

    public function setUp()
    {
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", '\MockPhpStream');
    }

    public function tearDown()
    {
        stream_wrapper_restore("php");
    }

    public function testFactory()
    {
        file_put_contents(
            'php://input',
            '{
                    "Timestamp" : "2012-04-25T21:49:25.719Z",
                      "SignatureVersion" : "1",
                      "Signature" : "true",
                      "SigningCertURL" : "dummy.pem",
                    "TopicArn":"arn",
                  "Type" : "Notification",
                  "MessageId" : "msg1",
                  "Message" : {
       "notificationType":"Delivery",
       "mail":{
          "timestamp":"2016-01-27T14:59:38.237Z",
          "source":"john@example.com",
          "sourceArn": "arn:aws:ses:us-west-2:888888888888:identity/example.com",
          "sourceIp": "127.0.3.0",
          "sendingAccountId":"123456789012",
          "messageId":"00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000",
          "destination":[
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"],
          "headersTruncated":false,
          "headers":[ 
           { 
             "name":"From",
             "value":"\"John Doe\" <john@example.com>"
           },
           { 
             "name":"To",
             "value":"\"Jane Doe\" <jane@example.com>, \"Mary Doe\" <mary@example.com>, \"Richard Doe\" <richard@example.com>"
           },
           { 
             "name":"Message-ID",
             "value":"custom-message-ID"
           },
           { 
             "name":"Subject",
             "value":"Hello"
           },
           { 
             "name":"Content-Type",
             "value":"text/plain; charset=\"UTF-8\""
           },
           { 
             "name":"Content-Transfer-Encoding",
             "value":"base64"
           },
           { 
             "name":"Date",
             "value":"Wed, 27 Jan 2016 14:05:45 +0000"
           }
          ],
          "commonHeaders":{ 
             "from":[ 
                "John Doe <john@example.com>"
             ],
             "date":"Wed, 27 Jan 2016 14:05:45 +0000",
             "to":[ 
                "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
             ],
             "messageId":"custom-message-ID",
             "subject":"Hello"
           }
        }
    }
                }'
        );
        $this->assertInstanceOf(
            \Teknasyon\AwsSesNotification\Email\DeliveryEmail::class,
            \Teknasyon\AwsSesNotification\Email\BaseEmail::factory(\Aws\Sns\Message::fromRawPostData()),
            'DeliveryEmail creation failed!'
        );
    }

    public function testGetReceipts()
    {
        file_put_contents(
            'php://input',
            '{
                    "Timestamp" : "2012-04-25T21:49:25.719Z",
                      "SignatureVersion" : "1",
                      "Signature" : "true",
                      "SigningCertURL" : "dummy.pem",
                    "TopicArn":"arn",
                  "Type" : "Notification",
                  "MessageId" : "msg1",
                  "Message" : {
       "notificationType":"Delivery",
       "mail":{
          "timestamp":"2016-01-27T14:59:38.237Z",
          "source":"john@example.com",
          "sourceArn": "arn:aws:ses:us-west-2:888888888888:identity/example.com",
          "sourceIp": "127.0.3.0",
          "sendingAccountId":"123456789012",
          "messageId":"00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000",
          "destination":[
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"],
          "headersTruncated":false,
          "headers":[ 
           { 
             "name":"From",
             "value":"\"John Doe\" <john@example.com>"
           },
           { 
             "name":"To",
             "value":"\"Jane Doe\" <jane@example.com>, \"Mary Doe\" <mary@example.com>, \"Richard Doe\" <richard@example.com>"
           },
           { 
             "name":"Message-ID",
             "value":"custom-message-ID"
           },
           { 
             "name":"Subject",
             "value":"Hello"
           },
           { 
             "name":"Content-Type",
             "value":"text/plain; charset=\"UTF-8\""
           },
           { 
             "name":"Content-Transfer-Encoding",
             "value":"base64"
           },
           { 
             "name":"Date",
             "value":"Wed, 27 Jan 2016 14:05:45 +0000"
           }
          ],
          "commonHeaders":{ 
             "from":[ 
                "John Doe <john@example.com>"
             ],
             "date":"Wed, 27 Jan 2016 14:05:45 +0000",
             "to":[ 
                "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
             ],
             "messageId":"custom-message-ID",
             "subject":"Hello"
           }
        }
    }
                }'
        );
        $mailObj = \Teknasyon\AwsSesNotification\Email\BaseEmail::factory(\Aws\Sns\Message::fromRawPostData());
        $this->assertEquals([],
            $mailObj->getReceipts(),
            'DeliveryEmail getReceipts failed!'
        );
        $this->assertEquals([
            "jane@example.com",
            "mary@example.com",
            "richard@example.com"],
            $mailObj->getDestination(),
            'DeliveryEmail getDestination failed!'
        );
        $this->assertTrue($mailObj->isDelivery(), 'DeliveryEmail isBounced failed!');
        $this->assertFalse($mailObj->shouldRemoved(), 'DeliveryEmail shouldRemoved failed!');
        $this->assertNull($mailObj->getSesMessage(), 'DeliveryEmail getSesMessage failed!');
        $this->assertEquals('john@example.com', $mailObj->getSource(),'DeliveryEmail getSource failed!');
        $this->assertEquals(
            '127.0.3.0',
            $mailObj->getSourceIp(),
            'DeliveryEmail getSourceIp failed!'
        );
        $this->assertEquals(
            '00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000',
            $mailObj->getMessageId(),
            'DeliveryEmail getMessageId failed!'
        );
        $this->assertEquals(
            [
                "from"=>[
        "John Doe <john@example.com>"
    ],
             "date"=>"Wed, 27 Jan 2016 14:05:45 +0000",
             "to"=>[
        "Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>"
    ],
             "messageId"=>"custom-message-ID",
             "subject"=>"Hello"
            ],
            $mailObj->getCommonHeaders(),
            'DeliveryEmail getCommonHeaders failed!'
        );
        $this->assertEquals(
            [
                "John Doe <john@example.com>"
            ],
            $mailObj->getHeaders('from'),
            'DeliveryEmail getHeaders failed!'
        );
    }
}
