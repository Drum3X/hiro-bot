<?php

namespace hiro\commands;

use Discord\DiscordCommandClient;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use hiro\CommandLoader;
use hiro\database\Database;

/**
 * Coinflip command class
 */
class Coinflip
{
    
    /**
     * command $category
     */
    private $category;
    
    /**
     * $client
     */
    private $discord;
    
    /**
     * __construct
     */
    public function __construct(DiscordCommandClient $client, CommandLoader $loader)
    {
        $this->category = "economy";
        $this->discord = $client;
        $client->registerCommand('coinflip', function($msg, $args)
        {
			include __DIR__ . '/../../db-settings.inc';
			$database = new Database($db_host, $db_dbname, $db_user, $db_pass);
            $embed = new Embed($this->discord);
			$usermoney = $database->getUserMoney($database->getUserIdByDiscordId($msg->author->id));
			if(!is_numeric($usermoney))
            {
                echo "money is empty" . PHP_EOL;
                if(!$database->addUser([
                    "discord_id" => $msg->author->id
                ]))
                {
                    $embed->setTitle('You are couldnt added to database.');
                    $msg->channel->sendEmbed($embed);
                    echo "cant added" . PHP_EOL;
                    return;
                }else
                {
                    echo "User added" . PHP_EOL;
                    $usermoney = 0;
                }
            }
            if(!$args[0] || !is_numeric($args[0]))
            {
                $embed->setColor('#ff0000');
                $embed->setDescription('You should type payment amount.');
            }else {
				if( $args[0] <= 0 )
				{
					$embed->setDescription("You should give a value greater than zero.");
					$embed->setColor('#ff0000');
				}else if( $args[0] > 50000 )
				{
					$embed->setDescription("You should give a value min than 50.000.");
					$embed->setColor('#ff0000');
				}else if( $args[0] > $usermoney )
				{
					$embed->setDescription("Your money isn't enough.");
					$embed->setColor('#ff0000');
				}else {
					$payamount = $args[0];
					$rand = rand(0, 1);
					
					// delete user money from payamount
					$database->setUserMoney($database->getUserIdByDiscordId($msg->author->id), $usermoney - $payamount);
					$usermoney = $usermoney - $payamount;
					
					if($rand)
					{
						$database->setUserMoney($database->getUserIdByDiscordId($msg->author->id), $usermoney + $payamount * 2);
						$embed->setDescription("You win " . $payamount * 2);
						$embed->setColor('#7CFC00');
					}else {
						$embed->setDescription("You lose " . $payamount);
						$embed->setColor('#ff0000');
					}
				}
            }
            $embed->setTimestamp();
            $msg->channel->sendEmbed($embed);
        }, [
            "aliases" => [
                "cf"
            ],
            "description" => "An economy game"
        ]);
    }
    
    public function __get(string $name)
    {
        return $this->{$name};
    }
    
}
