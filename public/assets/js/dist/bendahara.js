document.addEventListener("DOMContentLoaded",()=>{let d=[],k=[];try{const e=document.getElementById("trx-data");e&&(d=JSON.parse(e.dataset.transactions||"[]"));const t=document.getElementById("trx-categories");t&&(k=JSON.parse(t.dataset.categories||"[]"))}catch(e){console.error("Error parsing data:",e)}const b=e=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e).replace("Rp","Rp "),w=e=>new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"}),C=document.getElementById("btn-mobile-menu"),f=document.getElementById("sidebar-menu"),g=document.getElementById("sidebar-backdrop");if(C&&f&&g){const e=()=>{f.classList.remove("translate-x-0"),f.classList.add("-translate-x-full"),g.classList.add("hidden")};C.addEventListener("click",()=>{f.classList.remove("-translate-x-full"),f.classList.add("translate-x-0"),g.classList.remove("hidden")}),g.addEventListener("click",e)}let x="Semua",T="",i=1;const E=5,y=document.getElementById("table-body"),$=document.getElementById("page-info"),h=document.getElementById("btn-prev"),v=document.getElementById("btn-next"),I=document.getElementById("filter-container"),D=document.getElementById("search-input"),B=document.getElementById("btn-add-trx"),S=document.getElementById("summary-masuk"),M=document.getElementById("summary-keluar"),P=document.getElementById("summary-saldo"),N=document.getElementById("cashflowChart"),p=()=>{if(!y)return;let e=d.filter(n=>{const s=x==="Semua"||n.category===x,r=n.desc.toLowerCase().includes(T.toLowerCase());return s&&r});e.sort((n,s)=>new Date(s.date)-new Date(n.date)),H(e),O(e);const t=e.length,o=Math.ceil(t/E)||1;i>o&&(i=o);const l=(i-1)*E,u=e.slice(l,l+E);y.innerHTML="",u.length===0?y.innerHTML=`
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `:u.forEach(n=>{const s=document.createElement("tr");s.className="hover:bg-slate-50/50 transition-colors";const r=n.type==="in"?"text-blue-600":"text-rose-600",m=n.type==="in"?"+":"-",a=!document.getElementById("btn-add-trx");let c=`
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${n.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `;a||(c+=`
                        <a href="/keuangan/bendahara/wilayah/transaksi/edit/${n.id}" class="btn-edit-link p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="/keuangan/bendahara/wilayah/transaksi/delete/${n.id}" method="POST" class="inline">
                            <button type="submit" class="btn-delete-link p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" title="Hapus" onclick="event.preventDefault(); confirmDelete(this.closest('form'));">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    `),s.innerHTML=`
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${w(n.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${n.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${n.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${r} text-right">
                        ${m}${b(n.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${c}
                        </div>
                    </td>
                `,y.appendChild(s)}),$&&($.textContent=`Halaman ${i} dari ${o}`),h&&(h.disabled=i===1),v&&(v.disabled=i===o),document.querySelectorAll(".btn-detail").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);A(r)})}),document.querySelectorAll(".btn-edit").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);R(r)})}),document.querySelectorAll(".btn-delete").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);z(r)})})},H=e=>{if(!S||!M||!P)return;let t=0,o=0;e.forEach(l=>{l.type==="in"?t+=l.amount:o+=l.amount}),S.textContent=b(t),M.textContent=b(o),P.textContent=b(t-o)};let L=null;const O=e=>{if(!N)return;const t={};e.forEach(a=>{t[a.date]||(t[a.date]={in:0,out:0}),t[a.date][a.type]+=a.amount});const o=Object.keys(t).sort((a,c)=>new Date(a)-new Date(c)),l=o.map(a=>w(a)),u=o.map(a=>t[a].in),n=o.map(a=>t[a].out);L&&L.destroy();const s=N.getContext("2d"),r=s.createLinearGradient(0,0,0,300);r.addColorStop(0,"rgba(37, 99, 235, 1)"),r.addColorStop(1,"rgba(59, 130, 246, 0.1)");const m=s.createLinearGradient(0,0,0,300);m.addColorStop(0,"rgba(225, 29, 72, 1)"),m.addColorStop(1,"rgba(244, 63, 94, 0.1)"),L=new Chart(s,{type:"bar",data:{labels:l,datasets:[{label:"Pemasukan",data:u,backgroundColor:r,borderRadius:6,borderSkipped:!1,barThickness:16},{label:"Pengeluaran",data:n,backgroundColor:m,borderRadius:6,borderSkipped:!1,barThickness:16}]},options:{responsive:!0,maintainAspectRatio:!1,interaction:{mode:"index",intersect:!1},scales:{x:{grid:{display:!1,drawBorder:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#64748b"}},y:{beginAtZero:!0,grid:{color:"rgba(226, 232, 240, 0.8)",borderDash:[4,4],drawBorder:!1},border:{display:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#94a3b8",padding:10,callback:function(a){return a>=1e6?"Rp "+a/1e6+"Jt":a>=1e3?"Rp "+a/1e3+"K":"Rp "+a}}}},plugins:{legend:{position:"top",align:"end",labels:{usePointStyle:!0,boxWidth:8,boxHeight:8,font:{family:"'Inter', sans-serif",size:12,weight:"500"},color:"#475569",padding:20}},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.95)",titleFont:{family:"'Inter', sans-serif",size:13,weight:"600"},bodyFont:{family:"'Inter', sans-serif",size:13},padding:12,cornerRadius:12,usePointStyle:!0,boxPadding:6,callbacks:{label:function(a){let c=a.dataset.label||"";return c&&(c+=": "),a.parsed.y!==null&&(c+=b(a.parsed.y)),c}}}}}})};I&&I.addEventListener("click",e=>{e.target.tagName==="BUTTON"&&(x=e.target.dataset.cat,I.querySelectorAll("button").forEach(t=>{t.dataset.cat===x?t.className="category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all bg-[#3b5998] text-white shadow-md":t.className="category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100"}),i=1,p())}),D&&D.addEventListener("input",e=>{T=e.target.value,i=1,p()}),h&&h.addEventListener("click",()=>{i>1&&(i--,p())}),v&&v.addEventListener("click",()=>{i++,p()}),B&&B.addEventListener("click",e=>{B.tagName==="BUTTON"&&(e.preventDefault(),R())});const j=(e,t)=>{const o=document.getElementById(e);o&&o.remove();const l=document.createElement("div");return l.id=e,l.className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",l.innerHTML=t,document.body.appendChild(l),l.addEventListener("click",u=>{u.target===l&&l.remove()}),l},A=e=>{const t=d.find(l=>l.id===e);if(!t)return;const o=`
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
                        <p class="text-base font-semibold text-slate-900">${w(t.date)}</p>
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
                            ${b(t.amount)}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 text-right">
                    <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup</button>
                </div>
            </div>
        `;j("modal-detail",o)},R=(e=null)=>{let t=e!==null,o=t?d.find(s=>s.id===e):{date:new Date().toISOString().split("T")[0],desc:"",category:k[0]||"",type:"out",amount:""};const l=k.map(s=>`<option value="${s}" ${o.category===s?"selected":""}>${s}</option>`).join(""),u=`
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
                            <input type="text" id="form-desc" required value="${o.desc}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Contoh: Pembelian domain">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                                <input type="date" id="form-date" required value="${o.date}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
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
                                    <option value="in" ${o.type==="in"?"selected":""}>Pemasukan</option>
                                    <option value="out" ${o.type==="out"?"selected":""}>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" id="form-amount" required value="${o.amount}" min="0" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="500000">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-form').remove()">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        `,n=j("modal-form",u);n.querySelector("form").addEventListener("submit",s=>{s.preventDefault();const r={id:t?e:Date.now(),date:document.getElementById("form-date").value,desc:document.getElementById("form-desc").value,category:document.getElementById("form-cat").value,type:document.getElementById("form-type").value,amount:parseInt(document.getElementById("form-amount").value,10)};if(t){const m=d.findIndex(a=>a.id===e);m!==-1&&(d[m]=r)}else d.push(r);n.remove(),p()})},z=e=>{confirm("Apakah Anda yakin ingin menghapus transaksi ini?")&&(d=d.filter(t=>t.id!==e),p())};p()});
