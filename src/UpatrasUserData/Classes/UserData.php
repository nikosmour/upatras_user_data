<?php

namespace UpatrasUserData\Classes;

class UserData
{
    public array $output = [];
    public bool $success;




    private const EL_TO_EN = [
        "Επώνυμο" => "last_name",
        "Όνομα" => "first_name",
        "Πατρώνυμο" => "patronymic",
        "Ιδιότητα" => "status",
        "Τμήμα" => "department",
        "Κατάσταση" => "is_active",
        "Τίτλος" => "title",
        "Τηλέφωνο(α) εργασίας" => "work_phone",
        "Fax εργασίας" => "work_fax",
        "Κινητό τηλέφωνο" => "mobile_phone",
        "Τηλέφωνο(α) κατοικίας" => "home_phone",
        "Fax κατοικίας" => "home_fax",
        "Διεύθυνση κατοικίας" => "home_address",
        "Τηλέφωνο επικοινωνίας" => "contact_phone",
        "E-mail επικοινωνίας" => "email",
        "Αριθμός μητρώου" => "a_m",
        "Επώνυμο <i>(Λατινικά)</i>" => "last_name_latin",
        "Όνομα <i>(Λατινικά)</i>" => "first_name_latin"
    ];

    public function __construct(array $data )
    {
        $this->success = count( $data) > 0;
        if ($this->success) {
            $data['Κατάσταση'] = $data['Κατάσταση'] === 'Ενεργός';
            $data['Αριθμός μητρώου'] = (int)$data['Αριθμός μητρώου'];

//        // Loop through the incoming data and populate object properties dynamically
            $this->output = array_reduce(array_keys($data), function ($carry, $greekKey) use ($data) {
                if (isset($this::EL_TO_EN[$greekKey])) {
                    $carry[$this::EL_TO_EN[$greekKey]] = $data[$greekKey];
                }
                return $carry;
            }, []);
        }

    }

    // Convert the object to an array
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'output'=>$this->output,
        ];
    }

    // Convert the object to JSON
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    public function __toString(): string
    {
        return $this->toJson();
    }
}

