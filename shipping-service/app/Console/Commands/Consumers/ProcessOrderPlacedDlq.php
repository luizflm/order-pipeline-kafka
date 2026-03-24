<?php

namespace App\Console\Commands\Consumers;

use App\Actions\CreateShipment;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;
use Throwable;

class ProcessOrderPlacedDlq extends Command
{
    protected $signature = 'kafka:process-order-placed-dlq';
    protected $description = 'Process Order Placed messages from DLQ';

    public function __construct(private CreateShipment $action)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting DLQ recovery process');

        Kafka::consumer()
            ->subscribe('order.placed-dlq')
            ->withConsumerGroupId('shipping_service_dlq')
            ->withHandler(function (ConsumedMessage $message, $consumer) {
                try {
                    $data = $message->getBody();
                    
                    $this->action->handle($data);
                    
                    $this->info("Successfully processed order: " . $data['order_id']);
                    
                    $consumer->commit($message);
                } catch (Throwable $e) {
                    $this->error("Order processing failed again: " . $e->getMessage());
                }
            })
            ->build()
            ->consume();

        return Command::SUCCESS;
    }
}