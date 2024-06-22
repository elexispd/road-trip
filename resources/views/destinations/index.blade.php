@extends('layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            <h2>All Destination</h2>
            {{-- <a href="{{ route('destination.create') }}" class="btn btn-secondary ml-auto">Create</a> --}}
            <x-link href="{{ route('destination.create') }}">Create</x-link>
        </div>

        @include('layouts.alert')

        <div class="row" id="sortable">
            @forelse($destinations as $destination)
                <div class="col-md-4 mb-3" data-id="{{ $destination->id }}">
                    <div class="card">
                        <span class="rounded-circle bg-info d-inline-block text-center" style="width: 2rem; height: 2rem; line-height: 2rem;">{{ $destination->position }}</span>
                        <div class="card-body pt-0 text-center">
                            <h5 class="card-title">{{ $destination->destination }}</h5>
                            <p class="card-text">{{ $destination->location }}</p>
                            <form action="{{ route('destination.destroy', $destination->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"> No Destinations Available</h5>
                    </div>
                </div>
            @endforelse
        </div>

        <div id="map" style="height: 500px; width: 100%;"></div>

        <hr>


        <div class="card">
            <div class="card-body">
                <div id="route-info">
                    <h4 class="card-title">Destination Information</h4>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function() {
            // Initialize sortable list
            $("#sortable").sortable({
                update: function(event, ui) {
                    // Update destination order and display routes on map
                    updateDestinationOrder();
                }
            });

            // Function to update destination order and display routes on map
            function updateDestinationOrder() {
                var order = [];
                $('#sortable .col-md-4').each(function(index, element) {
                    var id = $(this).data('id');
                    var destination = $(this).find('.card-title').text();
                    order.push({ id: id, position: index + 1, destination: destination });
                });

                $.ajax({
                    url: "{{ route('destination.reorder') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order: order
                    },
                    success: function(response) {
                        // Display routes on map with the updated order
                        displayRoutesOnMap(order);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Handle error
                        console.log("Error: " + errorThrown);
                    }
                });
            }

            // Function to display routes on map
            function displayRoutesOnMap(order) {
                var directionsService = new google.maps.DirectionsService();
                var directionsRenderer = new google.maps.DirectionsRenderer();
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 6,
                    center: { lat: 6.5244, lng: 3.3792 } // Centered around Nigeria
                });
                directionsRenderer.setMap(map);

                var addresses = order.map(function(item) {
                    return item.destination;
                });

                // Initialize variables to hold waypoints
                var waypoints = [];
                var start = addresses[0];
                var end = addresses[addresses.length - 1];

                // Iterate through addresses to create waypoints
                for (var i = 1; i < addresses.length - 1; i++) {
                    waypoints.push({
                        location: addresses[i],
                        stopover: true
                    });
                }

                var request = {
                    origin: start,
                    destination: end,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode.DRIVING // You can change this to other modes like BICYCLING, TRANSIT, or WALKING
                };

                directionsService.route(request, function(response, status) {
                    if (status == 'OK') {
                        directionsRenderer.setDirections(response);
                        displayRouteInfo(response);
                    } else {
                        window.alert('Directions request failed due to ' + status);
                    }
                });

                $('#route-info').empty();
                // alert(directionsRenderer);
                calculateAndDisplayRoutes(addresses, directionsService, directionsRenderer);
            }

            // Function to calculate and display routes between each pair of destinations
            function calculateAndDisplayRoutes(addresses, directionsService, directionsRenderer) {
                // addresses = ["Manchester", "Norway", "Nigeria"];
                var routeInfoDiv = document.getElementById('route-info');

                // Create an array to store promises for directions requests
                var promises = [];

                // Loop through each pair of addresses to calculate routes
                for (var i = 0; i < addresses.length; i++) {
                    for (var j = i + 1; j < addresses.length; j++) {
                        var start = addresses[i];
                        var end = addresses[j];

                        // Create a closure to capture the current start and end addresses
                        (function(start, end) {
                            var request = {
                                origin: start,
                                destination: end,
                                travelMode: google.maps.TravelMode.DRIVING // Adjust as needed
                            };

                            // Create a promise for each directions request
                            var promise = new Promise(function(resolve, reject) {
                                directionsService.route(request, function(response, status) {
                                    if (status === 'OK') {
                                        // Display route on map
                                        directionsRenderer.setDirections(response);

                                        // Display route information
                                        displayRouteInfo(response, routeInfoDiv, start, end);

                                        // Calculate and display fuel information
                                        calculateAndDisplayFuel(response, routeInfoDiv);

                                        resolve(response);
                                    } else {
                                        reject(status);
                                    }
                                });
                            });

                            promises.push(promise);
                        })(start, end);
                    }
                }

                // Wait for all promises to resolve
                Promise.all(promises)
                    .then(function(results) {
                        // All routes calculated and displayed
                        console.log("All routes displayed");
                    })
                    .catch(function(error) {
                        // Handle errors
                        console.error("Error calculating routes: " + error);
                    });
            }

            // Function to display route information
            function displayRouteInfo(directionsResult, routeInfoDiv, start, end) {
                var route = directionsResult.routes[0];
                var leg = route.legs[0];

                var routeSummary = document.createElement('div');
                routeSummary.classList.add('route-summary');

                var routeTitle = document.createElement('h5');
                routeTitle.textContent = 'Route ' + leg.start_address + ' to ' + leg.end_address;

                var distance = leg.distance.text;
                var duration = leg.duration.text;

                var routeDetails = document.createElement('p');
                routeDetails.textContent = 'Distance: ' + distance + ', Duration: ' + duration;

                routeSummary.appendChild(routeTitle);
                routeSummary.appendChild(routeDetails);

                routeInfoDiv.appendChild(routeSummary);
            }

            // Function to calculate and display fuel information
            function calculateAndDisplayFuel(directionsResult, routeInfoDiv) {
                var route = directionsResult.routes[0];
                var totalDistance = 0;

                route.legs.forEach(function(leg) {
                    totalDistance += leg.distance.value;
                });

                // Convert totalDistance from meters to kilometers
                totalDistance = totalDistance / 1000;

                // Assuming fuel consumption of 10 liters per 100 km
                var fuelConsumption = totalDistance * 10 / 100;

                var fuelInfo = document.createElement('p');
                fuelInfo.textContent = 'Estimated Fuel Needed: ' + fuelConsumption.toFixed(2) + ' liters';

                routeInfoDiv.appendChild(fuelInfo);
            }

            // // Initial call to display routes on map with the original order
            updateDestinationOrder();
        });
    </script>


@endsection
