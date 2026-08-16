document.addEventListener("DOMContentLoaded",()=>{let c=[],I=[];try{const e=document.getElementById("trx-data");e&&(c=JSON.parse(e.dataset.transactions||"[]"));const t=document.getElementById("trx-categories");t&&(I=JSON.parse(t.dataset.categories||"[]"))}catch(e){console.error("Error parsing data:",e)}const p=e=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e).replace("Rp","Rp "),B=e=>new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"}),D=document.getElementById("btn-mobile-menu"),g=document.getElementById("sidebar-menu"),h=document.getElementById("sidebar-backdrop");if(D&&g&&h){const e=()=>{g.classList.remove("translate-x-0"),g.classList.add("-translate-x-full"),h.classList.add("hidden")};D.addEventListener("click",()=>{g.classList.remove("-translate-x-full"),g.classList.add("translate-x-0"),h.classList.remove("hidden")}),h.addEventListener("click",e)}let v="Semua",S="",i=1;const C=5,k=document.getElementById("table-body"),j=document.getElementById("page-info"),w=document.getElementById("btn-prev"),E=document.getElementById("btn-next"),L=document.getElementById("filter-container"),M=document.getElementById("search-input"),T=document.getElementById("btn-add-trx"),P=document.getElementById("summary-masuk"),N=document.getElementById("summary-keluar"),H=document.getElementById("summary-saldo"),R=document.getElementById("cashflowChart"),u=()=>{if(!k)return;let e=c.filter(n=>{const s=v==="Semua"||n.category===v,r=n.desc.toLowerCase().includes(S.toLowerCase());return s&&r});e.sort((n,s)=>new Date(s.date)-new Date(n.date)),z(e),q(e);const t=e.length,a=Math.ceil(t/C)||1;i>a&&(i=a);const l=(i-1)*C,d=e.slice(l,l+C);k.innerHTML="",d.length===0?k.innerHTML=`
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `:d.forEach(n=>{const s=document.createElement("tr");s.className="hover:bg-slate-50/50 transition-colors";const b=window.location.pathname.includes("unja")?"text-orange-600":"text-blue-600",m=n.type==="in"?b:"text-rose-600",x=n.type==="in"?"+":"-",y=!document.getElementById("btn-add-trx");let o=`
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${n.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `;y||(o+=`
                        <a href="/keuangan/bendahara/wilayah/transaksi/edit/${n.id}" class="btn-edit-link p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="/keuangan/bendahara/wilayah/transaksi/delete/${n.id}" method="POST" class="inline">
                            <button type="submit" class="btn-delete-link p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" title="Hapus" onclick="event.preventDefault(); confirmDelete(this.closest('form'));">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    `),s.innerHTML=`
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${B(n.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${n.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${n.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${m} text-right">
                        ${x}${p(n.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${o}
                        </div>
                    </td>
                `,k.appendChild(s)}),j&&(j.textContent=`Halaman ${i} dari ${a}`),w&&(w.disabled=i===1),E&&(E.disabled=i===a),document.querySelectorAll(".btn-detail").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);U(r)})}),document.querySelectorAll(".btn-edit").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);O(r)})}),document.querySelectorAll(".btn-delete").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);K(r)})})},z=e=>{if(!P||!N||!H)return;let t=0,a=0;e.forEach(l=>{l.type==="in"?t+=l.amount:a+=l.amount}),P.textContent=p(t),N.textContent=p(a),H.textContent=p(t-a)};let $=null;const q=e=>{if(!R)return;const t={};e.forEach(o=>{t[o.date]||(t[o.date]={in:0,out:0}),t[o.date][o.type]+=o.amount});const a=Object.keys(t).sort((o,f)=>new Date(o)-new Date(f)),l=a.map(o=>B(o)),d=a.map(o=>t[o].in),n=a.map(o=>t[o].out);$&&$.destroy();const s=window.location.pathname.includes("unja"),r=s?"rgba(234, 88, 12, 1)":"rgba(37, 99, 235, 1)",b=s?"rgba(249, 115, 22, 0.1)":"rgba(59, 130, 246, 0.1)",m=R.getContext("2d"),x=m.createLinearGradient(0,0,0,300);x.addColorStop(0,r),x.addColorStop(1,b);const y=m.createLinearGradient(0,0,0,300);y.addColorStop(0,"rgba(225, 29, 72, 1)"),y.addColorStop(1,"rgba(244, 63, 94, 0.1)"),$=new Chart(m,{type:"bar",data:{labels:l,datasets:[{label:"Pemasukan",data:d,backgroundColor:x,borderRadius:6,borderSkipped:!1,barThickness:16},{label:"Pengeluaran",data:n,backgroundColor:y,borderRadius:6,borderSkipped:!1,barThickness:16}]},options:{responsive:!0,maintainAspectRatio:!1,interaction:{mode:"index",intersect:!1},scales:{x:{grid:{display:!1,drawBorder:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#64748b"}},y:{beginAtZero:!0,grid:{color:"rgba(226, 232, 240, 0.8)",borderDash:[4,4],drawBorder:!1},border:{display:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#94a3b8",padding:10,callback:function(o){return o>=1e6?"Rp "+o/1e6+"Jt":o>=1e3?"Rp "+o/1e3+"K":"Rp "+o}}}},plugins:{legend:{position:"top",align:"end",labels:{usePointStyle:!0,boxWidth:8,boxHeight:8,font:{family:"'Inter', sans-serif",size:12,weight:"500"},color:"#475569",padding:20}},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.95)",titleFont:{family:"'Inter', sans-serif",size:13,weight:"600"},bodyFont:{family:"'Inter', sans-serif",size:13},padding:12,cornerRadius:12,usePointStyle:!0,boxPadding:6,callbacks:{label:function(o){let f=o.dataset.label||"";return f&&(f+=": "),o.parsed.y!==null&&(f+=p(o.parsed.y)),f}}}}}})};L&&L.addEventListener("click",e=>{if(e.target.tagName==="BUTTON"){v=e.target.dataset.cat;const a=window.location.pathname.includes("unja")?"bg-orange-600":"bg-[#3b5998]";L.querySelectorAll("button").forEach(l=>{l.dataset.cat===v?l.className=`category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all ${a} text-white shadow-md`:l.className="category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100"}),i=1,u()}}),M&&M.addEventListener("input",e=>{S=e.target.value,i=1,u()}),w&&w.addEventListener("click",()=>{i>1&&(i--,u())}),E&&E.addEventListener("click",()=>{i++,u()}),T&&T.addEventListener("click",e=>{T.tagName==="BUTTON"&&(e.preventDefault(),O())});const A=(e,t)=>{const a=document.getElementById(e);a&&a.remove();const l=document.createElement("div");return l.id=e,l.className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",l.innerHTML=t,document.body.appendChild(l),l.addEventListener("click",d=>{d.target===l&&l.remove()}),l},U=e=>{const t=c.find(s=>s.id===e);if(!t)return;const a=window.location.pathname.includes("unja"),l=a?"text-orange-600":"text-blue-600",d=a?"bg-orange-50 text-orange-700":"bg-blue-50 text-blue-700",n=`
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
                        <p class="text-base font-semibold text-slate-900">${B(t.date)}</p>
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
                        <p class="text-base font-semibold inline-flex px-2.5 py-0.5 rounded-full text-sm ${t.type==="in"?d:"bg-rose-50 text-rose-700"}">
                            ${t.type==="in"?"Pemasukan":"Pengeluaran"}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Nominal</p>
                        <p class="text-2xl font-bold ${t.type==="in"?l:"text-rose-600"}">
                            ${p(t.amount)}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 text-right">
                    <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup</button>
                </div>
            </div>
        `;A("modal-detail",n)},O=(e=null)=>{let t=e!==null,a=t?c.find(s=>s.id===e):{date:new Date().toISOString().split("T")[0],desc:"",category:I[0]||"",type:"out",amount:""};const l=I.map(s=>`<option value="${s}" ${a.category===s?"selected":""}>${s}</option>`).join(""),d=`
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
        `,n=A("modal-form",d);n.querySelector("form").addEventListener("submit",s=>{s.preventDefault();const r={id:t?e:Date.now(),date:document.getElementById("form-date").value,desc:document.getElementById("form-desc").value,category:document.getElementById("form-cat").value,type:document.getElementById("form-type").value,amount:parseInt(document.getElementById("form-amount").value,10)};if(t){const b=c.findIndex(m=>m.id===e);b!==-1&&(c[b]=r)}else c.push(r);n.remove(),u()})},K=e=>{confirm("Apakah Anda yakin ingin menghapus transaksi ini?")&&(c=c.filter(t=>t.id!==e),u())};u()});
