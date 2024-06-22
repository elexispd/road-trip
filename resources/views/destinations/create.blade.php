@extends('layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            <h2>Add Destination</h2>
            <x-link href="{{ route('destination.index') }}" >Home</x-link>
        </div>

        <form action="{{ route('destination.store') }}" method="POST">
            @csrf

            @include('layouts.alert')

            <div>
                <label for="name">Destination Name</label>
                <div id="destinations-container">
                    <div  class="form-group destination-input">
                        <input type="text" class="form-control" id="destination" name="destinations[]" placeholder="Enter destination name">
                    </div>
                </div>

                <i class="fa fa-circle-plus text-info add-destination"></i>
            </div>


            <button type="submit" class="btn btn-primary mt-3">Add Destination</button>

            <div id="map" style="height: 500px; width: 100%;"></div>

        </form>
    </div>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.add-destination', function() {

                var newDestinationInput = `
                    <div  class="form-group destination-input">
                        <input type="text" class="form-control" name="destinations[]" placeholder="Enter destination name" required>
                        <i class="fa fa-minus-circle text-danger remove-destination"></i>
                    </div>
                `;
                $('#destinations-container').append(newDestinationInput);
            });

            $(document).on('click', '.remove-destination', function() {
                $(this).closest('.destination-input').remove();
            });
        });
    </script>
@endsection
