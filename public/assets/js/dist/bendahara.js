document.addEventListener("DOMContentLoaded",()=>{let u=[],B=[];try{const t=document.getElementById("trx-data");t&&(u=JSON.parse(t.dataset.transactions||"[]"));const e=document.getElementById("trx-categories");e&&(B=JSON.parse(e.dataset.categories||"[]"))}catch(t){console.error("Error parsing data:",t)}const g=t=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(t).replace("Rp","Rp "),$=t=>new Date(t).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"}),S=document.getElementById("btn-mobile-menu"),f=document.getElementById("sidebar-menu"),y=document.getElementById("sidebar-backdrop");if(S&&f&&y){const t=()=>{f.classList.remove("translate-x-0"),f.classList.add("-translate-x-full"),y.classList.add("hidden")};S.addEventListener("click",()=>{f.classList.remove("-translate-x-full"),f.classList.add("translate-x-0"),y.classList.remove("hidden")}),y.addEventListener("click",t)}let k="Semua",M="",d=1;const C=5,w=document.getElementById("table-body"),P=document.getElementById("page-info"),E=document.getElementById("btn-prev"),I=document.getElementById("btn-next"),L=document.getElementById("filter-container"),N=document.getElementById("search-input"),T=document.getElementById("btn-add-trx"),A=document.getElementById("summary-masuk"),H=document.getElementById("summary-keluar"),R=document.getElementById("summary-saldo"),z=document.getElementById("cashflowChart"),p=()=>{if(!w)return;let t=u.filter(n=>{const s=k==="Semua"||n.category===k,r=n.desc.toLowerCase().includes(M.toLowerCase());return s&&r});t.sort((n,s)=>new Date(s.date)-new Date(n.date)),q(t),F(t);const e=t.length,a=Math.ceil(e/C)||1;d>a&&(d=a);const l=(d-1)*C,i=t.slice(l,l+C);w.innerHTML="",i.length===0?w.innerHTML=`
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `:i.forEach(n=>{const s=document.createElement("tr");s.className="hover:bg-slate-50/50 transition-colors";const r=window.location.pathname.includes("unja"),m=window.location.pathname.includes("uin"),b=r?"text-orange-600":m?"text-sky-500":"text-blue-600",x=n.type==="in"?b:"text-rose-600",h=n.type==="in"?"+":"-",v=!document.getElementById("btn-add-trx");let o=`
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${n.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `,c="/keuangan/bendahara/wilayah/transaksi",D="delete";r?(c="/keuangan/bendahara/unja/transaksi",D="hapus"):m&&(c="/keuangan/bendahara/uin/transaksi",D="hapus"),v||(o+=`
                        <a href="${c}/edit/${n.id}" class="btn-edit-link p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="${c}/${D}/${n.id}" method="POST" class="inline">
                            <button type="submit" class="btn-delete-link p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" title="Hapus" onclick="event.preventDefault(); confirmDelete(this.closest('form'));">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    `),s.innerHTML=`
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${$(n.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${n.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${n.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${x} text-right">
                        ${h}${g(n.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${o}
                        </div>
                    </td>
                `,w.appendChild(s)}),P&&(P.textContent=`Halaman ${d} dari ${a}`),E&&(E.disabled=d===1),I&&(I.disabled=d===a),document.querySelectorAll(".btn-detail").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);K(r)})}),document.querySelectorAll(".btn-edit").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);U(r)})}),document.querySelectorAll(".btn-delete").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);_(r)})})},q=t=>{if(!A||!H||!R)return;let e=0,a=0;t.forEach(l=>{l.type==="in"?e+=l.amount:a+=l.amount}),A.textContent=g(e),H.textContent=g(a),R.textContent=g(e-a)};let j=null;const F=t=>{if(!z)return;const e={};t.forEach(o=>{e[o.date]||(e[o.date]={in:0,out:0}),e[o.date][o.type]+=o.amount});const a=Object.keys(e).sort((o,c)=>new Date(o)-new Date(c)),l=a.map(o=>$(o)),i=a.map(o=>e[o].in),n=a.map(o=>e[o].out);j&&j.destroy();const s=window.location.pathname.includes("unja"),r=window.location.pathname.includes("uin"),m=s?"rgba(234, 88, 12, 1)":r?"rgba(14, 165, 233, 1)":"rgba(37, 99, 235, 1)",b=s?"rgba(249, 115, 22, 0.1)":r?"rgba(14, 165, 233, 0.1)":"rgba(59, 130, 246, 0.1)",x=z.getContext("2d"),h=x.createLinearGradient(0,0,0,300);h.addColorStop(0,m),h.addColorStop(1,b);const v=x.createLinearGradient(0,0,0,300);v.addColorStop(0,"rgba(225, 29, 72, 1)"),v.addColorStop(1,"rgba(244, 63, 94, 0.1)"),j=new Chart(x,{type:"bar",data:{labels:l,datasets:[{label:"Pemasukan",data:i,backgroundColor:h,borderRadius:6,borderSkipped:!1,barThickness:16},{label:"Pengeluaran",data:n,backgroundColor:v,borderRadius:6,borderSkipped:!1,barThickness:16}]},options:{responsive:!0,maintainAspectRatio:!1,interaction:{mode:"index",intersect:!1},scales:{x:{grid:{display:!1,drawBorder:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#64748b"}},y:{beginAtZero:!0,grid:{color:"rgba(226, 232, 240, 0.8)",borderDash:[4,4],drawBorder:!1},border:{display:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#94a3b8",padding:10,callback:function(o){return o>=1e6?"Rp "+o/1e6+"Jt":o>=1e3?"Rp "+o/1e3+"K":"Rp "+o}}}},plugins:{legend:{position:"top",align:"end",labels:{usePointStyle:!0,boxWidth:8,boxHeight:8,font:{family:"'Inter', sans-serif",size:12,weight:"500"},color:"#475569",padding:20}},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.95)",titleFont:{family:"'Inter', sans-serif",size:13,weight:"600"},bodyFont:{family:"'Inter', sans-serif",size:13},padding:12,cornerRadius:12,usePointStyle:!0,boxPadding:6,callbacks:{label:function(o){let c=o.dataset.label||"";return c&&(c+=": "),o.parsed.y!==null&&(c+=g(o.parsed.y)),c}}}}}})};L&&L.addEventListener("click",t=>{if(t.target.tagName==="BUTTON"){k=t.target.dataset.cat;const e=window.location.pathname.includes("unja"),a=window.location.pathname.includes("uin"),l=e?"bg-orange-600":a?"bg-sky-500":"bg-[#3b5998]";L.querySelectorAll("button").forEach(i=>{i.dataset.cat===k?i.className=`category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all ${l} text-white shadow-md`:i.className="category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100"}),d=1,p()}}),N&&N.addEventListener("input",t=>{M=t.target.value,d=1,p()}),E&&E.addEventListener("click",()=>{d>1&&(d--,p())}),I&&I.addEventListener("click",()=>{d++,p()}),T&&T.addEventListener("click",t=>{T.tagName==="BUTTON"&&(t.preventDefault(),U())});const O=(t,e)=>{const a=document.getElementById(t);a&&a.remove();const l=document.createElement("div");return l.id=t,l.className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",l.innerHTML=e,document.body.appendChild(l),l.addEventListener("click",i=>{i.target===l&&l.remove()}),l},K=t=>{const e=u.find(b=>b.id===t);if(!e)return;const a=window.location.pathname.includes("unja"),l=window.location.pathname.includes("uin"),i=a?"text-orange-600":l?"text-sky-500":"text-blue-600",n=a?"bg-orange-50 text-orange-700":l?"bg-sky-50 text-sky-700":"bg-blue-50 text-blue-700",s=a?"bg-orange-600 hover:bg-orange-700 text-white":l?"bg-sky-500 hover:bg-sky-600 text-white":"bg-blue-600 hover:bg-blue-700 text-white";let r='<p class="text-sm italic text-slate-400 mt-2">Tidak ada bukti transaksi disertakan.</p>';e.bukti_transaksi&&(r=`
                <a href="${e.bukti_transaksi.startsWith("http")?e.bukti_transaksi:"/uploads/keuangan/"+e.bukti_transaksi}" target="_blank" class="inline-flex items-center justify-center w-full px-4 py-3 mt-2 ${s} rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Lihat Bukti Transaksi
                </a>
            `);const m=`
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-slate-100">
                <div class="px-6 py-5 flex items-center justify-between border-b border-slate-50 bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 leading-tight">Detail Transaksi</h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">${e.jenis_entri==="invoice"?"Invoice / Tagihan":"Kegiatan"}</p>
                    </div>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200/50 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors" onclick="this.closest('#modal-detail').remove()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Tanggal</p>
                            <p class="text-sm font-semibold text-slate-900">${$(e.date)}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Status</p>
                            <p class="text-xs font-bold inline-flex px-3 py-1 rounded-full ${e.type==="in"?n:"bg-rose-50 text-rose-700"}">
                                ${e.type==="in"?"Pemasukan":"Pengeluaran"}
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Keterangan Transaksi</p>
                        <p class="text-[15px] font-semibold text-slate-900 leading-snug">${e.desc}</p>
                    </div>

                    <div class="flex items-center justify-between bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Alokasi Dana</p>
                            <p class="text-sm font-bold text-slate-700">${e.category}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Total Nominal</p>
                            <p class="text-xl font-black tracking-tight ${e.type==="in"?i:"text-rose-600"}">
                                ${g(e.amount)}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-3">Bukti Lampiran</p>
                        <div class="bg-white rounded-2xl p-1">
                            ${r}
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50/80 text-right border-t border-slate-50 flex justify-end gap-3">
                    <button class="px-5 py-2.5 bg-white text-slate-600 border border-slate-200/60 shadow-sm rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup Detail</button>
                </div>
            </div>
        `;O("modal-detail",m)},U=(t=null)=>{let e=t!==null,a=e?u.find(s=>s.id===t):{date:new Date().toISOString().split("T")[0],desc:"",category:B[0]||"",type:"out",amount:""};const l=B.map(s=>`<option value="${s}" ${a.category===s?"selected":""}>${s}</option>`).join(""),i=`
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
                <form id="trx-form">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">${e?"Edit Transaksi":"Tambah Transaksi"}</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#modal-form').remove()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / Nama Kegiatan</label>
                            <input type="text" id="form-desc" required value="${a.desc}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Contoh: Pembelian domain">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                                <input type="date" id="form-date" required value="${a.date}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Divisi</label>
                                <select id="form-cat" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    ${l}
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Transaksi</label>
                                <select id="form-type" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="in" ${a.type==="in"?"selected":""}>Pemasukan</option>
                                    <option value="out" ${a.type==="out"?"selected":""}>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" id="form-amount" required value="${a.amount}" min="0" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="500000">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-form').remove()">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        `,n=O("modal-form",i);n.querySelector("form").addEventListener("submit",s=>{s.preventDefault();const r={id:e?t:Date.now(),date:document.getElementById("form-date").value,desc:document.getElementById("form-desc").value,category:document.getElementById("form-cat").value,type:document.getElementById("form-type").value,amount:parseInt(document.getElementById("form-amount").value,10)};if(e){const m=u.findIndex(b=>b.id===t);m!==-1&&(u[m]=r)}else u.push(r);n.remove(),p()})},_=t=>{confirm("Apakah Anda yakin ingin menghapus transaksi ini?")&&(u=u.filter(e=>e.id!==t),p())};p()});
