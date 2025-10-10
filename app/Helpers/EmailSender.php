<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class EmailSender
{
    public $template;
    public $data = [];
    public $from;
    public $name_sender;
    public $to;
    public $subject;
    public $name;
    public $file;
    public $res;

    public function sendEmail()
    {
        try {
            $template = $this->template;
            $data = (array) $this->data;
            $name_sender = $this->name_sender;
            $from = $this->from;
            $to = $this->to;
            $subject = $this->subject;
            $name = (!empty($this->name) ? $this->name : 'Member');

            Mail::send($template, $data, function ($email) use ($from, $name_sender, $to, $subject, $name) {
                $email->priority(1);
                $email->to($to, $name)->subject($subject);
                $email->from($from, $name_sender);
            });

            return $this->res = "send";
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    public function sendEmailWithFile()
    {
        try {
            $template = $this->template;
            $data = (array) $this->data;
            $name_sender = $this->name_sender;
            $from = $this->from;
            $to = $this->to;
            $subject = $this->subject;
            $name = (!empty($this->name) ? $this->name : 'Member');
            $file = $this->file;

            Mail::send($template, $data, function ($email) use ($from, $name_sender, $to, $subject, $name, $file) {
                $email->priority(1);
                $email->to($to, $name)->subject($subject);
                $email->from($from, $name_sender);
                $email->attach($file);
            });

            return $this->res = "send";
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    /**
     * ✅ Function baru: kirim email dari file HTML mentah
     */
    public function sendEmailHTML()
    {
        try {
            $templatePath = resource_path('views/' . $this->template . '.html');

            if (!file_exists($templatePath)) {
                throw new \Exception("HTML template not found at {$templatePath}");
            }

            // ambil isi file html
            $html = File::get($templatePath);

            // replace placeholder {{ $key }} atau [[key]]
            foreach ((array) $this->data as $key => $value) {
                $patterns = [
                    '/\{\{\s*\$?' . preg_quote($key, '/') . '\s*\}\}/', // {{ key }} atau {{ $key }}
                    '/\[\[\s*' . preg_quote($key, '/') . '\s*\]\]/',   // [[key]]
                ];
                $html = preg_replace($patterns, $value, $html);
            }

            Mail::send([], [], function ($email) use ($html) {
                $email->to($this->to, $this->name ?? null)
                    ->from($this->from, $this->name_sender)
                    ->subject($this->subject)
                    ->setBody($html, 'text/html');
            });

            return "send";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
