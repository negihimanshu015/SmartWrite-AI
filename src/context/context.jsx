import { createContext } from "react";
import run from "../api/api";

export const Context = createContext();

const ContextProvider = (props) => {

    const onsent = async (prompt) => {
        await run(prompt)
    }
    
    const ContextValue = {}

    return(
        <Context.Provider value={ContextValue}>
            {props.children}
        </Context.Provider>
    )
 }

 export default ContextProvider