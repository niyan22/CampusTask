<!DOCTYPE html>
<html>
    <head>
        <title>Campus Task Manager</title>
        <style>
            .cards {
                display: flex;
                gap: 30px;
            }
            .card {
                padding: 20px;
                border: 1px solid;
                border-radius: 10px;
                box-shadow: 0 2px 5px gray;
                text-align: center;
                width: 200px;
            }
        </style>
    </head>
    <body>
        <h1>Welcome to Campus Task Manager</h1>
        <p>Kelola Tugas Kuliahmu Dengan Lebih Mudah</p>
        <div class="cards">
            <div class="card">
                <h2 id="total">8</h2>
                <p>Semua Tugas</p>
            </div>
            <div class="card">
                <h2 id="belumSelesai">3</h2>
                <p>Tugas Belum Selesai</p>
            </div>
            <div class="card">
                <h2 id="selesai">5</h2>
                <p id="pesan">Tugas Selesai</p>
            </div>
        </div>
        <script>
            let totalTugas = 15;
            let tugasBelumSelesai = 7;
            let tugasSelesai = 3;

            if (tugasSelesai >= 5) {
                console.log("Keren! Tugas kamu sudah banyak yang selesai.");
                document.getElementById("pesan").textContent = "Keren! Tugas kamu sudah banyak yang selesai.";
            } else {
                console.log("Ayo semangat menyelesaikan tugasmu!");
                document.getElementById("pesan").textContent = "Ayo semangat menyelesaikan tugasmu!";
            }

            document.getElementById("total").textContent = totalTugas;
            document.getElementById("belumSelesai").textContent = tugasBelumSelesai;
            document.getElementById("selesai").textContent = tugasSelesai;
            console.log(totalTugas);
            console.log(tugasBelumSelesai);
            console.log(tugasSelesai);
        </script>      
    </body>
</html>