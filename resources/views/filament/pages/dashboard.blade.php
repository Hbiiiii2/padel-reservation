<x-filament-panels::page>
    {{-- Inisialisasi Alpine component untuk real-time clock --}}
    <div 
        x-data="{ 
            // Inisialisasi waktu awal dari PHP/Server
            currentTime: '{{ \Carbon\Carbon::now()->format('H:i:s T') }}',

            // Fungsi untuk memperbarui waktu setiap detik menggunakan JavaScript
            updateTime() {
                const now = new Date();
                
                // Format jam (24-jam), menit, dan detik dengan leading zero
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                
                // Mendapatkan singkatan zona waktu dari browser (mungkin tidak selalu akurat, jadi diubah ke 'Local' jika tidak ada)
                const timezone = now.toLocaleTimeString('en-us', { timeZoneName:'short' }).split(' ')[2] || 'Local';

                this.currentTime = `${hours}:${minutes}:${seconds} ${timezone}`;
            }
        }"
        x-init="
            updateTime(); // Jalankan pertama kali
            setInterval(() => updateTime(), 1000); // Jadwalkan update setiap 1000ms (1 detik)
        "
        class="fi-current-time bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl p-6 mb-6"
    >
        <h2 class="text-xl font-semibold text-gray-950 dark:text-white mb-2">
            Current Time (Real-Time)
        </h2>
        {{-- Mengikat elemen ini ke variabel Alpine.js `currentTime` --}}
        <p x-text="currentTime" class="text-4xl font-bold text-primary-600 dark:text-primary-400">
            </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
           
        </p>
    </div>

    {{-- Pastikan widget atau konten dashboard lain Anda ditambahkan di bawah sini --}}

</x-filament-panels::page>