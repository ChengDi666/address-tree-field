<?php

Route::get('/{resource}/{resourceId}/attached/{relationship}/{idKey}', 'Norgeit\AddressTreeField\Http\Controllers\NestedTreeController@attached');
