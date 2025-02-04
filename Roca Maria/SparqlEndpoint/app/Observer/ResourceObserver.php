// namespace App\Observers;

// use App\Models\Resource;
// use Illuminate\Support\Facades\Log;

// class ResourceObserver
// {
// public function created(Resource $resource)
// {
// Log::info('Resource created: ' . $resource->getUri());

// // Example: Notify other systems via API
// $this->notifyExternalSystem($resource, 'created');
// }

// public function updated(Resource $resource)
// {
// Log::info('Resource updated: ' . $resource->getUri());

// // Additional logic for handling updates
// $this->notifyExternalSystem($resource, 'updated');
// }

// public function deleted(Resource $resource)
// {
// Log::warning('Resource deleted: ' . $resource->getUri());

// // Additional logic for handling deletions
// $this->notifyExternalSystem($resource, 'deleted');
// }

// private function notifyExternalSystem(Resource $resource, $event)
// {
// // Example logic for notifying an external system
// $payload = [
// 'uri' => $resource->getUri(),
// 'type' => $resource->getType(),
// 'event' => $event,
// ];

// // Hypothetical HTTP request
// // \Http::post('https://example.com/api/resources', $payload);
// Log::info('Notified external system: ' . json_encode($payload));
// }
// }