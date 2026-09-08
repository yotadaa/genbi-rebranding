import DriveImage from "@/components/DriveImage";
import TemplatePage from "@/components/template/page";
import { Head } from "@inertiajs/react";

interface Prestasi {
    tanggal: string;      // format date-time string
    nama: string;
    prodi: string;
    kampus: string;
    acara: string;
    prestasi: string;
    link: string[];       // bisa 1 atau lebih link
    sosmed: string;       // bisa URL / username
}



export default function PrestasiPage({ prestasi }: { prestasi: Prestasi[]; }) {


    return (
        <TemplatePage>
            <div className="px-4 sm:px-6 lg:px-8 py-8 lg:max-w-7xl w-full mx-auto">
                <Head title="Berita GenBI - Official GenBI Jambi" />
                <div className="mb-6 relative flex items-center justify-center">
                    {/* Judul */}
                    <h1 className="text-3xl font-serif leading-tight tracking-tight font-bold">GenBI Prestasi</h1>
                </div>
                <div className={`flex flex-wrap gap-2 justify-center `}>
                    {prestasi.map((p, i) => (
                        <CardOverlay key={i} item={p} />
                    ))}
                </div>
            </div>
        </TemplatePage>
    )
}

function CardOverlay({ item }: { item: Prestasi }) {
    return (
        <div className="h-[400px] w-[400px]">
            <article className="group relative w-full h-full overflow-hidden rounded shadow-sm py-5">
                {/* image */}
                {item.link.map((o, i) => (
                    <DriveImage
                        key={i}
                        driveUrl={o}
                        className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        alt={`prestasi-${i}`}
                    />
                ))}



                {/* gradient overlay */}
                <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/100 via-black/30  to-transparent transition-all duration-300 ease-in-out" />

                {/* text overlay */}
                <div className="absolute bottom-0 left-0 right-0 p-4 text-white h-2/5 flex flex-col justify-between transition-all duration-300 ease-in-out group-hover:-translate-y-full">
                    <div>
                        <h3 className="font-serif text-lg font-semibold leading-tight tracking-tight ">
                            {item.nama}
                        </h3>
                        <h4 className="font-serif text-base font-semibold leading-tight tracking-tight ">
                            {item.acara}
                        </h4>
                        <p className="mt-1 text-sm text-white/90 line-clamp-2 group-hover:line-clamp-none drop-shadow transition-all duration-300 ease-in-out">
                            {item.prestasi ?? "Short deskripsi singkat berita…"}
                        </p>
                    </div>

                    <div className=''>
                        <span className="font-extrabold text-sm border border-white inline-block px-2 py-1 rounded bg-black/50 group-hover:bg-white/20">
                            {item.kampus}
                        </span>
                    </div>
                </div>
            </article>
        </div>

    );
}