<!DOCTYPE html>
<html>

<head>
    <title>Peta Kabupaten</title>
    <script src="https://code.highcharts.com/maps/highmaps.js"></script>
    <script src="https://code.highcharts.com/maps/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
    <style>
        #container {
            height: 600px;
            min-width: 310px;
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div id="container"></div>

    <script>
        let districts = {};
        let villages = {};

        async function loadAllMapData() {
            try {
                // Memuat data distrik dari API
                const distrikResponse = await fetch('/api/districts');
                districts = await distrikResponse.json();
                console.info(districts)

                // Membuat permintaan data kampung untuk setiap distrik
                const villagePromises = districts.features.map(geometry => {
                    const districtId = geometry.properties.distrik_id;
                    return fetch(`/api/villages?districtId=${districtId}`);
                });

                // Jalankan semua fetch request secara paralel
                const responses = await Promise.all(villagePromises);

                // Konversi response ke json dan simpan dalam objek kampung
                const jsonPromises = responses.map(async (response, index) => {
                    const districtId = districts.features[index].properties.distrik_id;
                    villages[districtId] = await response.json();
                });

                // Tunggu semua json conversion selesai
                await Promise.all(jsonPromises);

                // Inisialisasi peta dengan data yang dimuat
                initializeChart();
            } catch (error) {
                console.error("Error loading map data: ", error);
            }
        }


        function initializeChart() {
            Highcharts.mapChart('container', {
                // Pengaturan navigasi dan data peta
                title: {
                    text: 'Peta Kabupaten'
                },
                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        verticalAlign: 'bottom'
                    }
                },
                colorAxis: {
                    min: 0
                },
                plotOptions: {
                    map: {
                        dataLabels: {
                            enabled: true,
                            format: '{point.properties.name}'
                        }
                    }
                },
                series: [{
                    data: districts.features.map(feature => {
                        return {
                            distrik_id: feature.properties.distrik_id,
                            name: feature.properties.name,
                            value: (feature.properties.distrik_id * 12345) % 100,
                            drilldown: feature.properties.distrik_id // Menambahkan referensi drilldown
                        };
                    }),
                    mapData: districts,
                    joinBy: 'distrik_id',
                    name: 'Kabupaten',
                    states: {
                        hover: {
                            color: '#a4edba'
                        }
                    },
                }],
                drilldown: {
                    activeDataLabelStyle: {
                        color: '#FFFFFF',
                        textDecoration: 'none',
                        textOutline: '1px #000000'
                    },
                    breadcrumbs: {
                        floating: true,
                        showFullPath: false
                    },
                    mapZooming: true,
                    series: districts.features.map(feature => {
                        const districtId = feature.properties.distrik_id;
                        return {
                            id: districtId,
                            name: feature.properties.name,
                            mapData: villages[districtId],
                            data: villages[districtId].features.map(feature => {
                                return {
                                    desa_id: feature.properties.desa_id,
                                    name: feature.properties.name,
                                    value: (feature.properties.desa_id * 12345) % 100,
                                };
                            }),
                            joinBy: 'desa_id'
                        };
                    }),
                },
            });
        }

        // Memulai proses memuat data
        loadAllMapData();
    </script>
</body>

</html>
