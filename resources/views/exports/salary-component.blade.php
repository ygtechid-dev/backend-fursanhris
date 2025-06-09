<table>
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Salary Component ID</th>
            <th>Salary Component Name</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
           @foreach ($employee->allowances as $allowance)
               <tr>
                <td>{{$employee->id}}</td>
                <td>{{$employee->name}}</td>
                <td>{{$allowance->id}}</td>
                <td>{{$allowance->title}}</td>
                <td>allowance</td>
                <td>{{$allowance->amount}}</td>
                <td>{{$allowance->type}}</td>
               </tr>
           @endforeach
           @foreach ($employee->deductions as $deduction)
               <tr>
                <td>{{$employee->id}}</td>
                <td>{{$employee->name}}</td>
                <td>{{$deduction->id}}</td>
                <td>{{$deduction->title}}</td>
                <td>deduction</td>
                <td>{{$deduction->amount}}</td>
                <td>{{$deduction->type}}</td>
               </tr>
           @endforeach
        @endforeach
    </tbody>
</table>