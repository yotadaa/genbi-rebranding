document.addEventListener("DOMContentLoaded",()=>{let i=[],y=[];try{const e=document.getElementById("trx-data");e&&(i=JSON.parse(e.dataset.transactions||"[]"));const t=document.getElementById("trx-categories");t&&(y=JSON.parse(t.dataset.categories||"[]"))}catch(e){console.error("Error parsing data:",e)}const m=e=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e).replace("Rp","Rp "),h=e=>new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"}),B=document.getElementById("btn-mobile-menu"),p=document.getElementById("sidebar-menu"),b=document.getElementById("sidebar-backdrop");if(B&&p&&b){const e=()=>{p.classList.remove("translate-x-0"),p.classList.add("-translate-x-full"),b.classList.add("hidden")};B.addEventListener("click",()=>{p.classList.remove("-translate-x-full"),p.classList.add("translate-x-0"),b.classList.remove("hidden")}),b.addEventListener("click",e)}let f="Semua",L="",r=1;const k=5,g=document.getElementById("table-body"),$=document.getElementById("page-info"),x=document.getElementById("btn-prev"),v=document.getElementById("btn-next"),w=document.getElementById("filter-container"),C=document.getElementById("search-input"),D=document.getElementById("btn-add-trx"),T=document.getElementById("summary-masuk"),M=document.getElementById("summary-keluar"),S=document.getElementById("summary-saldo"),j=document.getElementById("cashflowChart"),u=()=>{if(!g)return;let e=i.filter(n=>{const o=f==="Semua"||n.category===f,a=n.desc.toLowerCase().includes(L.toLowerCase());return o&&a});e.sort((n,o)=>new Date(o.date)-new Date(n.date)),H(e),R(e);const t=e.length,s=Math.ceil(t/k)||1;r>s&&(r=s);const l=(r-1)*k,c=e.slice(l,l+k);g.innerHTML="",c.length===0?g.innerHTML=`
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `:c.forEach(n=>{const o=document.createElement("tr");o.className="hover:bg-slate-50/50 transition-colors";const a=n.type==="in"?"text-blue-600":"text-rose-600",d=n.type==="in"?"+":"-",I=!document.getElementById("btn-add-trx");let A=`
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${n.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `;I||(A+=`
                        <button type="button" class="btn-edit p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" data-id="${n.id}" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button type="button" class="btn-delete p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" data-id="${n.id}" title="Hapus">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    `),o.innerHTML=`
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${h(n.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${n.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${n.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${a} text-right">
                        ${d}${m(n.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${A}
                        </div>
                    </td>
                `,g.appendChild(o)}),$&&($.textContent=`Halaman ${r} dari ${s}`),x&&(x.disabled=r===1),v&&(v.disabled=r===s),document.querySelectorAll(".btn-detail").forEach(n=>{n.addEventListener("click",o=>{const a=parseInt(o.currentTarget.dataset.id,10);q(a)})}),document.querySelectorAll(".btn-edit").forEach(n=>{n.addEventListener("click",o=>{const a=parseInt(o.currentTarget.dataset.id,10);P(a)})}),document.querySelectorAll(".btn-delete").forEach(n=>{n.addEventListener("click",o=>{const a=parseInt(o.currentTarget.dataset.id,10);O(a)})})},H=e=>{if(!T||!M||!S)return;let t=0,s=0;e.forEach(l=>{l.type==="in"?t+=l.amount:s+=l.amount}),T.textContent=m(t),M.textContent=m(s),S.textContent=m(t-s)};let E=null;const R=e=>{if(!j)return;const t={};e.forEach(a=>{t[a.date]||(t[a.date]={in:0,out:0}),t[a.date][a.type]+=a.amount});const s=Object.keys(t).sort((a,d)=>new Date(a)-new Date(d)),l=s.map(a=>h(a)),c=s.map(a=>t[a].in),n=s.map(a=>t[a].out);E&&E.destroy();const o=j.getContext("2d");E=new Chart(o,{type:"bar",data:{labels:l,datasets:[{label:"Pemasukan",data:c,backgroundColor:"#2563eb",borderRadius:4},{label:"Pengeluaran",data:n,backgroundColor:"#e11d48",borderRadius:4}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,ticks:{callback:function(a){return"Rp "+a/1e6+"Jt"}}}},plugins:{legend:{position:"top"},tooltip:{callbacks:{label:function(a){let d=a.dataset.label||"";return d&&(d+=": "),a.parsed.y!==null&&(d+=m(a.parsed.y)),d}}}}}})};w&&w.addEventListener("click",e=>{e.target.tagName==="BUTTON"&&(f=e.target.dataset.cat,w.querySelectorAll("button").forEach(t=>{t.dataset.cat===f?t.className="category-btn px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-md shadow-blue-200":t.className="category-btn px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100"}),r=1,u())}),C&&C.addEventListener("input",e=>{L=e.target.value,r=1,u()}),x&&x.addEventListener("click",()=>{r>1&&(r--,u())}),v&&v.addEventListener("click",()=>{r++,u()}),D&&D.addEventListener("click",()=>{P()});const N=(e,t)=>{const s=document.getElementById(e);s&&s.remove();const l=document.createElement("div");return l.id=e,l.className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",l.innerHTML=t,document.body.appendChild(l),l.addEventListener("click",c=>{c.target===l&&l.remove()}),l},q=e=>{const t=i.find(l=>l.id===e);if(!t)return;const s=`
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
                    <button class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#modal-detail').remove()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tanggal</p>
                        <p class="text-base font-semibold text-slate-900">${h(t.date)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Keterangan Kegiatan</p>
                        <p class="text-base font-semibold text-slate-900">${t.desc}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Divisi / Kategori</p>
                        <p class="text-base font-semibold text-slate-900">${t.category}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tipe Transaksi</p>
                        <p class="text-base font-semibold inline-flex px-2.5 py-0.5 rounded-full text-sm ${t.type==="in"?"bg-blue-50 text-blue-700":"bg-rose-50 text-rose-700"}">
                            ${t.type==="in"?"Pemasukan":"Pengeluaran"}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Nominal</p>
                        <p class="text-2xl font-bold ${t.type==="in"?"text-blue-600":"text-rose-600"}">
                            ${m(t.amount)}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 text-right">
                    <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup</button>
                </div>
            </div>
        `;N("modal-detail",s)},P=(e=null)=>{let t=e!==null,s=t?i.find(o=>o.id===e):{date:new Date().toISOString().split("T")[0],desc:"",category:y[0]||"",type:"out",amount:""};const l=y.map(o=>`<option value="${o}" ${s.category===o?"selected":""}>${o}</option>`).join(""),c=`
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
                <form id="trx-form">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">${t?"Edit Transaksi":"Tambah Transaksi"}</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#modal-form').remove()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / Nama Kegiatan</label>
                            <input type="text" id="form-desc" required value="${s.desc}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Contoh: Pembelian domain">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                                <input type="date" id="form-date" required value="${s.date}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
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
                                    <option value="in" ${s.type==="in"?"selected":""}>Pemasukan</option>
                                    <option value="out" ${s.type==="out"?"selected":""}>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" id="form-amount" required value="${s.amount}" min="0" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="500000">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-form').remove()">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        `,n=N("modal-form",c);n.querySelector("form").addEventListener("submit",o=>{o.preventDefault();const a={id:t?e:Date.now(),date:document.getElementById("form-date").value,desc:document.getElementById("form-desc").value,category:document.getElementById("form-cat").value,type:document.getElementById("form-type").value,amount:parseInt(document.getElementById("form-amount").value,10)};if(t){const d=i.findIndex(I=>I.id===e);d!==-1&&(i[d]=a)}else i.push(a);n.remove(),u()})},O=e=>{confirm("Apakah Anda yakin ingin menghapus transaksi ini?")&&(i=i.filter(t=>t.id!==e),u())};u()});
