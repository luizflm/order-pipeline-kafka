<?php

namespace App\Console\Commands\Consumers;

use App\Actions\CreateShipment;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;

class OrderPlacedConsumer extends Command
{
    protected $signature = 'kafka:consume-order-placed';
    protected $description = 'Consume Kafka topic messages';

    public function __construct(private CreateShipment $action) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Initializing consumer of template requested...');

        Kafka::consumer()
            ->subscribe('order.placed')
            ->withConsumerGroupId('shipping_service_consumer')
            ->withHandler(function (ConsumedMessage $message) {
                $data = $message->getBody();

                $this->action->handle($data);

                echo "Message received: " . $data['order_id']  . PHP_EOL;
            })
            ->build()
            ->consume();

        return Command::SUCCESS;
    }
}