import{c as d,j as e,H as m}from"./app-BndNHLne.js";import{a as t,c as n}from"./button-B938siKt.js";import{S as u,H as h}from"./layout-BYd9v7Mz.js";import{A as x}from"./app-layout-Bta0KnNJ.js";/* empty css            */import"./separator-DOf9pYrm.js";import"./index-DI4Ye8VL.js";import"./app-header-layout-DF_mBwyX.js";import"./index-QVslWZ3W.js";import"./app-logo-icon-Dajt3FdR.js";/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const y=[["rect",{width:"20",height:"14",x:"2",y:"3",rx:"2",key:"48i651"}],["line",{x1:"8",x2:"16",y1:"21",y2:"21",key:"1svkeh"}],["line",{x1:"12",x2:"12",y1:"17",y2:"21",key:"vw1qmm"}]],k=t("Monitor",y);/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const g=[["path",{d:"M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z",key:"a7tn18"}]],b=t("Moon",g);/**
 * @license lucide-react v0.475.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const f=[["circle",{cx:"12",cy:"12",r:"4",key:"4exip2"}],["path",{d:"M12 2v2",key:"tus03m"}],["path",{d:"M12 20v2",key:"1lh1kg"}],["path",{d:"m4.93 4.93 1.41 1.41",key:"149t6j"}],["path",{d:"m17.66 17.66 1.41 1.41",key:"ptbguv"}],["path",{d:"M2 12h2",key:"1t8f8n"}],["path",{d:"M20 12h2",key:"1q8mjw"}],["path",{d:"m6.34 17.66-1.41 1.41",key:"1m8zz5"}],["path",{d:"m19.07 4.93-1.41 1.41",key:"1shlcs"}]],j=t("Sun",f);function v({className:r="",...i}){const{appearance:o,updateAppearance:c}=d(),s=[{value:"light",icon:j,label:"Claro"},{value:"dark",icon:b,label:"Oscuro"},{value:"system",icon:k,label:"Sistema"}];return e.jsx("div",{className:n("inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800",r),...i,children:s.map(({value:a,icon:l,label:p})=>e.jsxs("button",{onClick:()=>c(a),className:n("flex items-center rounded-md px-3.5 py-1.5 transition-colors",o===a?"bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100":"text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60"),children:[e.jsx(l,{className:"-ml-1 h-4 w-4"}),e.jsx("span",{className:"ml-1.5 text-sm",children:p})]},a))})}const A=[{title:"Configuración de Apariencia",href:"/settings/appearance"}];function T(){return e.jsxs(x,{breadcrumbs:A,children:[e.jsx(m,{title:"Configuración de Apariencia"}),e.jsx(u,{children:e.jsxs("div",{className:"space-y-6",children:[e.jsx(h,{title:"Configuración de Apariencia",description:"Actualiza la configuración de apariencia de tu cuenta"}),e.jsx(v,{})]})})]})}export{T as default};
