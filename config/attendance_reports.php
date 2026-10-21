<?php

return [
    'daily-attendance' => [
        'title' => 'Daily Attendance Report', 'table' => 'daily_attendances', 'date_column' => 'date', 'status_column' => 'status',
        'columns' => [
            'employee_id'=>'Employee ID', 'business_unit' => 'Business Unit', 'employee_name'=>'Employee Name', 'designation'=>'Designation', 'date'=>'Date',
            'shift_name'=>'Shift Name', 'scheduled_in_time'=>'Scheduled In Time', 'scheduled_out_time'=>'Scheduled Out Time',
            'actual_punch_in'=>'Actual Punch In', 'actual_punch_out'=>'Actual Punch Out', 'duration'=>'Duration',
            'duration_calculation_method'=>'Duration Calculation Method', 'late_in_by'=>'Late In By', 'early_out_by'=>'Early Out By',
            'overtime_worked'=>'Overtime Worked', 'overtime_eligible'=>'Overtime Eligible', 'overtime_type'=>'Overtime Type',
            'status'=>'Status', 'payment_status'=>'Payment Status',
        ],
    ],
    'on-duty' => [
        'title' => 'On Duty Report', 'table'=>'on_duties', 'date_column'=>'raised_for', 'status_column'=>'approval_status',
        'columns'=>[
            'employee_id'=>'Employee ID','employee_name'=>'Employee Name','designation'=>'Designation','raised_on'=>'Raised On',
            'raised_for'=>'Raised For','request_id'=>'Request ID','shift_name'=>'Shift Name','on_duty_type'=>'On Duty Type',
            'on_duty_start_time'=>'Start Time','on_duty_end_time'=>'End Time','on_duty_period'=>'On Duty Period',
            'reason'=>'Reason','approval_status'=>'Approval Status','action_taken_by_id'=>'Action By ID',
            'action_taken_by_name'=>'Action By Name',
        ],
    ],
    'leave-requests' => [
        'title'=>'Leave Requests Report','table'=>'leave_requests','date_column'=>'leave_start_date','status_column'=>'approval_status',
        'columns'=>[
            'employee_id'=>'Employee ID','employee_name'=>'Employee Name','leave_start_date'=>'Leave Start Date',
            'leave_end_date'=>'Leave End Date','leave_type'=>'Leave Type','leave_type_status'=>'Leave Type Status',
            'duration'=>'Duration','leave_credits'=>'Leave Credits','approval_status'=>'Approval Status',
            'raised_on'=>'Raised On','leave_reason'=>'Leave Reason','final_approval'=>'Final Approval',
        ],
    ],
    'attendance-regularization' => [
        'title'=>'Attendance Regularization Report','table'=>'attendance_regularizations','date_column'=>'raised_for','status_column'=>'approval_status',
        'columns'=>[
            'employee_id'=>'Employee ID','employee_name'=>'Employee Name','designation'=>'Designation','raised_on'=>'Raised On',
            'raised_for'=>'Raised For','assigned_shift_name'=>'Assigned Shift','assigned_in_time'=>'Assigned In',
            'assigned_out_time'=>'Assigned Out','assigned_shift_duration'=>'Assigned Duration',
            'actual_shift_name'=>'Actual Shift','actual_in_time'=>'Actual In','actual_out_time'=>'Actual Out',
            'actual_duration'=>'Actual Duration','regularized_shift_name'=>'Regularized Shift',
            'regularized_in_time'=>'Regularized In','regularized_out_time'=>'Regularized Out',
            'regularized_duration'=>'Regularized Duration','reason'=>'Reason','approval_status'=>'Approval Status',
            'action_taken_by_id'=>'Action By ID','action_taken_by_name'=>'Action By Name',
        ],
    ],
];
