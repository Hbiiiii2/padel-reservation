<x-filament-panels::page>
    <div 
        x-data="{ 
            currentTime: '{{ \Carbon\Carbon::now()->format('H:i:s T') }}',

            updateTime() {
                const now = new Date();
                
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                
                // Mendapatkan singkatan zona waktu dari browser (mungkin tidak selalu akurat, jadi diubah ke 'Local' jika tidak ada)
                const timezone = now.toLocaleTimeString('en-us', { timeZoneName:'short' }).split(' ')[2] || 'Local';

                this.currentTime = `${hours}:${minutes}:${seconds}`;
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
        <p x-text="currentTime" class="text-xl font-bold text-primary-600 dark:text-primary-400">
            </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
           
        </p>
    </div>

    {{-- Pastikan widget atau konten dashboard lain Anda ditambahkan di bawah sini --}}

</x-filament-panels::page>