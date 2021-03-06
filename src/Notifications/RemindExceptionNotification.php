<?php

namespace Pyaesone17\Lapse\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Exception;

class RemindExceptionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($exception, $formatting = [])
    {
        $this->exception = $exception;
        $this->formatting = $formatting;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return config('lapse.via');
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('lapse.index');
        
        $title = sprintf("%s found at %s", 
            $this->exception->getMessage(),
            url()->current()
        );

        $class = get_class($this->exception);

        return (new MailMessage)
                    ->line($title)
                    ->line('Exception Type : '.$class)
                    ->action('View', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = route('lapse.index');

        $title = sprintf("%s found at %s", 
            $this->exception->getMessage(),
            url()->current()
        );

        return (new SlackMessage)
            ->error()
            ->from('Laravel Lapse', ':see_no_evil:')
            ->content($this->exception->getMessage())
            ->attachment(function ($attachment) use ($title, $url) {
                $attachment->title($title, $url)
                ->content($this->exception->__toString());
            });
    }

    public function __call($name, $arguments)
    {
        if(array_key_exists($name,$this->formatting)){
            $formatter = $this->formatting[$name];
            if(is_callable($formatter) === false){
                throw new NotificationFormatterException("Notification formatter must be callable");
            }
            return $formatter($arguments[0]);
        }
    }
}
