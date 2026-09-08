document.addEventListener("DOMContentLoaded",()=>{(()=>{const v=document.querySelector('[data-dashboard="true"]');if(!v)return;let u="Semua",h="",o=1;const p=5;let y=[];try{y=JSON.parse(v.dataset.transactions||"[]")}catch(t){console.error("Failed to parse transactions",t)}let x=null;const g=document.getElementById("search-input"),w=document.querySelectorAll(".category-btn"),b=document.getElementById("table-body"),E=document.getElementById("total-in"),k=document.getElementById("total-out"),C=document.getElementById("total-balance"),r=document.getElementById("prev-page"),i=document.getElementById("next-page"),I=document.getElementById("page-indicator"),c=t=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(t),B=()=>{let t=y;if(u!=="Semua"&&(t=t.filter(e=>e.category===u)),h.trim()!==""){const e=h.toLowerCase();t=t.filter(n=>n.desc.toLowerCase().includes(e)||n.category.toLowerCase().includes(e))}return t},L=t=>{const e=t.filter(a=>a.type==="in").reduce((a,d)=>a+d.amount,0),n=t.filter(a=>a.type==="out").reduce((a,d)=>a+d.amount,0),s=e-n;E&&(E.textContent=c(e)),k&&(k.textContent=c(n)),C&&(C.textContent=c(s))},$=t=>{if(!b)return;const e=Math.max(1,Math.ceil(t.length/p));o>e&&(o=Math.max(1,e));const n=(o-1)*p,s=t.slice(n,n+p);b.innerHTML="",s.length===0?b.innerHTML='<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Tidak ada transaksi ditemukan</td></tr>':s.forEach(a=>{const d=document.createElement("tr");d.innerHTML=`
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${a.date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">${a.desc}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${a.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${a.type==="in"?"text-emerald-600":"text-red-600"} text-right">
                            ${a.type==="in"?"+":"-"}${c(a.amount)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button type="button" class="btn-detail text-blue-600 hover:text-blue-800" data-id="${a.id}">Detail</button>
                        </td>
                    `,b.appendChild(d)}),I&&(I.textContent=`${o} dari ${e}`),r&&(r.disabled=o===1),i&&(i.disabled=o===e),document.querySelectorAll(".btn-detail").forEach(a=>{a.addEventListener("click",d=>{const f=parseInt(d.target.dataset.id,10);N(f)})})},N=t=>{const e=y.find(a=>a.id===t);if(!e)return;const n=document.getElementById("trx-modal");n&&n.remove();const s=document.createElement("div");s.id="trx-modal",s.className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm",s.innerHTML=`
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
                        <button class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#trx-modal').remove()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Tanggal</p>
                            <p class="text-base font-semibold text-slate-900">${e.date}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Keterangan Kegiatan</p>
                            <p class="text-base font-semibold text-slate-900">${e.desc}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Divisi / Kategori</p>
                            <p class="text-base font-semibold text-slate-900">${e.category}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Tipe Transaksi</p>
                            <p class="text-base font-semibold ${e.type==="in"?"text-emerald-600":"text-red-600"}">
                                ${e.type==="in"?"Pemasukan":"Pengeluaran"}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Nominal</p>
                            <p class="text-xl font-bold ${e.type==="in"?"text-emerald-600":"text-red-600"}">
                                ${c(e.amount)}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right">
                        <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300" onclick="this.closest('#trx-modal').remove()">Tutup</button>
                    </div>
                </div>
            `,document.body.appendChild(s),s.addEventListener("click",a=>{a.target===s&&s.remove()})},M=t=>{const e=document.getElementById("financeChart");if(!e)return;const n={};t.forEach(l=>{n[l.date]||(n[l.date]={in:0,out:0}),n[l.date][l.type]+=l.amount});const s=Object.keys(n).sort(),a=s.map(l=>n[l].in),d=s.map(l=>n[l].out),f={labels:s,datasets:[{label:"Pemasukan",data:a,backgroundColor:"rgba(16, 185, 129, 0.8)",borderRadius:4},{label:"Pengeluaran",data:d,backgroundColor:"rgba(239, 68, 68, 0.8)",borderRadius:4}]};if(x)x.data=f,x.update();else{const l=e.getContext("2d");x=new Chart(l,{type:"bar",data:f,options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"top"}},scales:{y:{beginAtZero:!0}}}})}},m=()=>{const t=B();L(t),$(t),M(t),w.forEach(e=>{e.dataset.cat===u?e.className="category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-md shadow-blue-200":e.className="category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100"})};if(g){const t=g.cloneNode(!0);g.parentNode.replaceChild(t,g),t.addEventListener("input",e=>{h=e.target.value,o=1,m()})}if(w.forEach(t=>{const e=t.cloneNode(!0);t.parentNode.replaceChild(e,t),e.addEventListener("click",()=>{u=e.dataset.cat,o=1,m()})}),r){const t=r.cloneNode(!0);r.parentNode.replaceChild(t,r),t.addEventListener("click",()=>{o>1&&(o--,m())})}if(i){const t=i.cloneNode(!0);i.parentNode.replaceChild(t,i),t.addEventListener("click",()=>{const e=B(),n=Math.max(1,Math.ceil(e.length/p));o<n&&(o++,m())})}m()})()});
