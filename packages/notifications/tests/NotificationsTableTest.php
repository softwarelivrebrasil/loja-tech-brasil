<?php

use Illuminate\Support\Facades\Schema;

it('creates the notifications table', function () {
    expect(Schema::hasTable('notifications'))->toBeTrue()
        ->and(Schema::hasColumns('notifications', [
            'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at',
        ]))->toBeTrue();
});
