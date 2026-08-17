document.addEventListener("DOMContentLoaded",()=>{let u=[],B=[];try{const e=document.getElementById("trx-data");e&&(u=JSON.parse(e.dataset.transactions||"[]"));const t=document.getElementById("trx-categories");t&&(B=JSON.parse(t.dataset.categories||"[]"))}catch(e){console.error("Error parsing data:",e)}const b=e=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e).replace("Rp","Rp "),C=e=>new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"}),j=document.getElementById("btn-mobile-menu"),f=document.getElementById("sidebar-menu"),v=document.getElementById("sidebar-backdrop");if(j&&f&&v){const e=()=>{f.classList.remove("translate-x-0"),f.classList.add("-translate-x-full"),v.classList.add("hidden")};j.addEventListener("click",()=>{f.classList.remove("-translate-x-full"),f.classList.add("translate-x-0"),v.classList.remove("hidden")}),v.addEventListener("click",e)}let k="Semua",M="",d=1;const L=5,w=document.getElementById("table-body"),P=document.getElementById("page-info"),E=document.getElementById("btn-prev"),I=document.getElementById("btn-next"),$=document.getElementById("filter-container"),N=document.getElementById("search-input"),T=document.getElementById("btn-add-trx"),A=document.getElementById("summary-masuk"),H=document.getElementById("summary-keluar"),R=document.getElementById("summary-saldo"),O=document.getElementById("cashflowChart"),m=()=>{if(!w)return;let e=u.filter(n=>{const s=k==="Semua"||n.category===k,r=n.desc.toLowerCase().includes(M.toLowerCase());return s&&r});e.sort((n,s)=>new Date(s.date)-new Date(n.date)),q(e),K(e);const t=e.length,a=Math.ceil(t/L)||1;d>a&&(d=a);const l=(d-1)*L,i=e.slice(l,l+L);w.innerHTML="",i.length===0?w.innerHTML=`
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `:i.forEach(n=>{const s=document.createElement("tr");s.className="hover:bg-slate-50/50 transition-colors";const r=window.location.pathname.includes("unja"),p=window.location.pathname.includes("uin"),g=r?"text-orange-600":p?"text-sky-500":"text-blue-600",x=n.type==="in"?g:"text-rose-600",y=n.type==="in"?"+":"-",h=!document.getElementById("btn-add-trx");let o=`
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${n.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `,c="/keuangan/bendahara/wilayah/transaksi",S="delete";r?(c="/keuangan/bendahara/unja/transaksi",S="hapus"):p&&(c="/keuangan/bendahara/uin/transaksi",S="hapus"),h||(o+=`
                        <a href="${c}/edit/${n.id}" class="btn-edit-link p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="${c}/${S}/${n.id}" method="POST" class="inline">
                            <button type="submit" class="btn-delete-link p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" title="Hapus" onclick="event.preventDefault(); confirmDelete(this.closest('form'));">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    `),s.innerHTML=`
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${C(n.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${n.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${n.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${x} text-right">
                        ${y}${b(n.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${o}
                        </div>
                    </td>
                `,w.appendChild(s)}),P&&(P.textContent=`Halaman ${d} dari ${a}`),E&&(E.disabled=d===1),I&&(I.disabled=d===a),document.querySelectorAll(".btn-detail").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);F(r)})}),document.querySelectorAll(".btn-edit").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);z(r)})}),document.querySelectorAll(".btn-delete").forEach(n=>{n.addEventListener("click",s=>{const r=parseInt(s.currentTarget.dataset.id,10);J(r)})})},q=e=>{if(!A||!H||!R)return;let t=0,a=0;e.forEach(l=>{l.type==="in"?t+=l.amount:a+=l.amount}),A.textContent=b(t),H.textContent=b(a),R.textContent=b(t-a)};let D=null;const K=e=>{if(!O)return;const t={};e.forEach(o=>{t[o.date]||(t[o.date]={in:0,out:0}),t[o.date][o.type]+=o.amount});const a=Object.keys(t).sort((o,c)=>new Date(o)-new Date(c)),l=a.map(o=>C(o)),i=a.map(o=>t[o].in),n=a.map(o=>t[o].out);D&&D.destroy();const s=window.location.pathname.includes("unja"),r=window.location.pathname.includes("uin"),p=s?"rgba(234, 88, 12, 1)":r?"rgba(14, 165, 233, 1)":"rgba(37, 99, 235, 1)",g=s?"rgba(249, 115, 22, 0.1)":r?"rgba(14, 165, 233, 0.1)":"rgba(59, 130, 246, 0.1)",x=O.getContext("2d"),y=x.createLinearGradient(0,0,0,300);y.addColorStop(0,p),y.addColorStop(1,g);const h=x.createLinearGradient(0,0,0,300);h.addColorStop(0,"rgba(225, 29, 72, 1)"),h.addColorStop(1,"rgba(244, 63, 94, 0.1)"),D=new Chart(x,{type:"bar",data:{labels:l,datasets:[{label:"Pemasukan",data:i,backgroundColor:y,borderRadius:6,borderSkipped:!1,barThickness:16},{label:"Pengeluaran",data:n,backgroundColor:h,borderRadius:6,borderSkipped:!1,barThickness:16}]},options:{responsive:!0,maintainAspectRatio:!1,interaction:{mode:"index",intersect:!1},scales:{x:{grid:{display:!1,drawBorder:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#64748b"}},y:{beginAtZero:!0,grid:{color:"rgba(226, 232, 240, 0.8)",borderDash:[4,4],drawBorder:!1},border:{display:!1},ticks:{font:{family:"'Inter', sans-serif",size:12},color:"#94a3b8",padding:10,callback:function(o){return o>=1e6?"Rp "+o/1e6+"Jt":o>=1e3?"Rp "+o/1e3+"K":"Rp "+o}}}},plugins:{legend:{position:"top",align:"end",labels:{usePointStyle:!0,boxWidth:8,boxHeight:8,font:{family:"'Inter', sans-serif",size:12,weight:"500"},color:"#475569",padding:20}},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.95)",titleFont:{family:"'Inter', sans-serif",size:13,weight:"600"},bodyFont:{family:"'Inter', sans-serif",size:13},padding:12,cornerRadius:12,usePointStyle:!0,boxPadding:6,callbacks:{label:function(o){let c=o.dataset.label||"";return c&&(c+=": "),o.parsed.y!==null&&(c+=b(o.parsed.y)),c}}}}}})};$&&$.addEventListener("click",e=>{if(e.target.tagName==="BUTTON"){k=e.target.dataset.cat;const t=window.location.pathname.includes("unja"),a=window.location.pathname.includes("uin"),l=t?"bg-orange-600":a?"bg-sky-500":"bg-[#3b5998]";$.querySelectorAll("button").forEach(i=>{i.dataset.cat===k?i.className=`category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all ${l} text-white shadow-md`:i.className="category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100"}),d=1,m()}}),N&&N.addEventListener("input",e=>{M=e.target.value,d=1,m()}),E&&E.addEventListener("click",()=>{d>1&&(d--,m())}),I&&I.addEventListener("click",()=>{d++,m()}),T&&T.addEventListener("click",e=>{T.tagName==="BUTTON"&&(e.preventDefault(),z())});const U=(e,t)=>{const a=document.getElementById(e);a&&a.remove();const l=document.createElement("div");return l.id=e,l.className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",l.innerHTML=t,document.body.appendChild(l),l.addEventListener("click",i=>{i.target===l&&l.remove()}),l},F=e=>{const t=u.find(r=>r.id===e);if(!t)return;const a=window.location.pathname.includes("unja"),l=window.location.pathname.includes("uin"),i=a?"text-orange-600":l?"text-sky-500":"text-blue-600",n=a?"bg-orange-50 text-orange-700":l?"bg-sky-50 text-sky-700":"bg-blue-50 text-blue-700",s=`
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
                        <p class="text-base font-semibold text-slate-900">${C(t.date)}</p>
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
                        <p class="text-base font-semibold inline-flex px-2.5 py-0.5 rounded-full text-sm ${t.type==="in"?n:"bg-rose-50 text-rose-700"}">
                            ${t.type==="in"?"Pemasukan":"Pengeluaran"}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Nominal</p>
                        <p class="text-2xl font-bold ${t.type==="in"?i:"text-rose-600"}">
                            ${b(t.amount)}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 text-right">
                    <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup</button>
                </div>
            </div>
        `;U("modal-detail",s)},z=(e=null)=>{let t=e!==null,a=t?u.find(s=>s.id===e):{date:new Date().toISOString().split("T")[0],desc:"",category:B[0]||"",type:"out",amount:""};const l=B.map(s=>`<option value="${s}" ${a.category===s?"selected":""}>${s}</option>`).join(""),i=`
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
        `,n=U("modal-form",i);n.querySelector("form").addEventListener("submit",s=>{s.preventDefault();const r={id:t?e:Date.now(),date:document.getElementById("form-date").value,desc:document.getElementById("form-desc").value,category:document.getElementById("form-cat").value,type:document.getElementById("form-type").value,amount:parseInt(document.getElementById("form-amount").value,10)};if(t){const p=u.findIndex(g=>g.id===e);p!==-1&&(u[p]=r)}else u.push(r);n.remove(),m()})},J=e=>{confirm("Apakah Anda yakin ingin menghapus transaksi ini?")&&(u=u.filter(t=>t.id!==e),m())};m()});
