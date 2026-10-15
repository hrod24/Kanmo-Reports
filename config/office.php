<?php

return [

  'allowed_ips' => array_values(
    array_filter(
      array_map(
        'trim',
        explode(
          ',',
          env('ALLOWED_OFFICE_IPS', '')
        )
      )
    )
  ),

];
