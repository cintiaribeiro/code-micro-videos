import { Chip } from '@material-ui/core';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import React , {useState, useEffect}from 'react';
import { httpVideo } from '../../util/http';
import format from "date-fns/format";
import parseISO from 'date-fns/parseISO';

const columsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "is_active",
        label: "Ativo?",
        options:{
            customBodyRender(value, tableMeta, updateValue){
                return value ? <Chip label="Sim" color="primary"/> : <Chip label="Não" color="secondary"/>
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em",
        options:{
            customBodyRender(value, tableMeta, updateValue){
            return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    }

];
const data = [
    { name: "Teste1", is_active: true, created_at: "2020-09-10"},
    { name: "Teste2", is_active: false, created_at: "2020-09-10"},
    { name: "Teste3", is_active: true, created_at: "2020-09-10"},
    { name: "Teste4", is_active: false, created_at: "2020-09-10"},
]

type Props = {

};
const Table = (props: Props) => {

    const [data, setData ] = useState([]);
    
    useEffect(()=>{
        httpVideo.get('categories').then(
            response =>  setData(response.data.data)
        )
    }, []);

    return (
        <div>
            <MUIDataTable 
                title="Listagem de categorias"
                columns={columsDefinition}
                data={data}
            />
        </div>
    );
}

export default Table;