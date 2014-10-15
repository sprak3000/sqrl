<?php

$tables = array();
$tables['sqrl_nonce']=array();
$tables['sqrl_nonce'][]=array('id'=>1,
    'nonce'=>'some localhost delivered nonce',
    'created'=>date('Y-m-d H:i:s'),
    'ip'=>ip2long('127.0.0.1'),
    'action'=>1,
    'related_public_key'=>null,
    'verified'=>0
    );
$tables['sqrl_nonce'][]=array('id'=>2,
    'nonce'=>'some 192 delivered nonce',
    'created'=>date('Y-m-d H:i:s'),
    'ip'=>ip2long('192.168.0.1'),
    'action'=>1,
    'related_public_key'=>null,
    'verified'=>0
    );
$tables['sqrl_nonce'][]=array('id'=>3,
    'nonce'=>'some stale nonce',
    'created'=>'2011-01-01 00:00:00',
    'ip'=>ip2long('192.168.0.1'),
    'action'=>1,
    'related_public_key'=>null,
    'verified'=>0
    );

$tables['sqrl_pubkey']=array();
$tables['sqrl_pubkey'][]=array('id'=>1,
    'public_key'=>'xLOjlTKNdYFkCx+OMQT7hSoK7Ta54ioKZgWrh2ig0Fs=',
    'vuk'=>null,
    'suk'=>null,
    'disabled'=>0
    );
$tables['sqrl_pubkey'][]=array('id'=>2,
    'public_key'=>'another public key value',
    'vuk'=>null,
    'suk'=>null,
    'disabled'=>0
    );
$tables['sqrl_pubkey'][]=array('id'=>3,
    'public_key'=>'MmDImzNYkpmVk7/Bjw4/WEBWec4rSlOjQvJLfYfGdBs=',
    'vuk'=>null,
    'suk'=>null,
    'disabled'=>0
    );

return $tables;